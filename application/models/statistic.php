<?php
/**
 * Model used on dashboard, it is designed more in functional approach
 * (it could be changed in the time), but for now it serves results for
 * basic statistics provided on Home of application.
 */
class Statistic_Model extends Model {

	public static function get_resource_statistic($type, $curator_id = NULL, $year = NULL, $month = NULL)
	{
		switch ($type)
		{
			case 'contracts' :
				return self::get_contracts($year, $month);
				break;
			case 'rated' :
				return self::get_rated($year, $month);
				break;
			case 'approved' :
				return self::get_approved($year, $month);
				break;
			default :
				$settings = self::get_settings($type);
		}
		$conditions = array();
		if (isset($settings ['select_column']) AND ! is_null($settings ['select_column']))
		{
			$select_column = $settings ['select_column'];
		} else
		{
			$select_column = 'r.id';
		}

		if (! is_null($settings ['conditions']))
		{
			$conditions [] = $settings ['conditions'];
		}
		if (! is_null($curator_id))
		{
			if ($type == 'suggested')
			{
				$conditions [] = "creator_id = {$curator_id}";
			} elseif ($type == 'ratings')
			{
				$conditions [] = "t.curator_id = {$curator_id}";
			} else
			{
				$conditions [] = "r.curator_id = {$curator_id}";
			}
		}

		$date_conditions = self::create_date_conditions($settings['date_column'], $year, $month);
		$conditions = array_merge($conditions, $date_conditions);

		$distinct = (isset($settings ['distinct']) AND $settings ['distinct'] === TRUE) ? 'DISTINCT' : '';

		$where = self::combine_conditions($conditions, 'WHERE');
		$sql = "SELECT {$distinct} count({$select_column}) as 'count' FROM resources r{$settings['tables_add']} {$where}";
		return sql::get_first_result($sql)->count;
	}

	public static function get_contracts($year = NULL, $month = NULL)
	{
		$conditions = self::create_date_conditions('date_signed', $year, $month);
		$conditions[] = 'parent_id is null';
		$where = self::combine_conditions($conditions, 'WHERE');

		$sql = "SELECT count(c.id) as 'count' FROM contracts c {$where}";
		return sql::get_first_result($sql)->count;
	}

	public static function get_rated($year = NULL, $month = NULL)
	{
		$date_conditions = self::create_date_conditions('last_date', $year, $month);
		$where = self::combine_conditions($date_conditions, 'HAVING');

		$sql = "SELECT id, MIN(date) AS last_date FROM ratings GROUP BY resource_id, round_id {$where}";
		return sql::get_result($sql)->count();
	}

	protected static function get_addressed()
	{
		$settings ['conditions'] = 'r.id = c.resource_id AND correspondence_type_id = 1';
		$settings ['tables_add'] = ', correspondence c';
		$settings ['date_column'] = 'c.date';
		$settings ['distinct'] = TRUE;
		return $settings;
	}

	protected static function get_resources()
	{
		$settings ['conditions'] = NULL;
		$settings ['tables_add'] = NULL;
		$settings ['date_column'] = 'date';
		return $settings;
	}

	protected static function get_approved($year = NULL, $month = NULL)
	{
		// we need to split resources on those with any ratings and without any
		// these resources are rated and the last rating was in selected month
		$g_date_column = 'MAX(g.date)';
		$having_date_conditions = self::create_date_conditions($g_date_column, $year, $month);
		$having = self::combine_conditions($having_date_conditions, 'HAVING');

		// these resources aren't rated (migrated from old system),
		// are approved and was created in selected month
		$r_date_column = 'r.date';
		$resource_conditions = self::create_date_conditions($r_date_column, $year, $month);
		$resource_conditions[] = "resource_id IS NULL";
		$resource_conditions[] = "rating_result =2";
		$where = self::combine_conditions($resource_conditions, 'WHERE');

		$sql = "(
                    SELECT r.id
                    FROM resources r, ratings g
                    WHERE rating_result =2
                    AND r.id = g.resource_id
                    GROUP BY r.id, g.round_id
                    {$having}
                )
                UNION (
                    SELECT r.id
                    FROM resources r
                    LEFT JOIN ratings ON ( resource_id = r.id )
                    {$where}
                )";
		return sql::get_result($sql)->count();
	}

	protected static function get_catalogued()
	{
		$settings ['conditions'] = 'catalogued IS NOT NULL';
		$settings ['tables_add'] = NULL;
		$settings ['date_column'] = 'catalogued';
		return $settings;
	}

	protected static function get_suggested($suggested_by = 'curator')
	{
		switch ($suggested_by)
		{
			case 'ISSN' :
				$suggested_by_id = 4;
				break;
			case 'web-visitor' :
				$suggested_by_id = 3;
				break;
			case 'web-publisher' :
				$suggested_by_id = 2;
				break;
			case 'curator' :
			default :
				$suggested_by_id = 1;
				break;
		}
		$settings ['conditions'] = "suggested_by_id = {$suggested_by_id}";
		$settings ['tables_add'] = NULL;
		$settings ['date_column'] = 'date';
		return $settings;
	}

	protected static function get_correspondence()
	{
		$settings ['conditions'] = 'r.id = c.resource_id';
		$settings ['tables_add'] = ', correspondence c';
		$settings ['date_column'] = 'c.date';
		$settings ['select_column'] = 'c.id';
		return $settings;
	}

	protected static function get_contracted()
	{
		$settings ['conditions'] = 'r.contract_id = c.id';
		$settings ['tables_add'] = ', contracts c';
		$settings ['date_column'] = 'c.date_signed';
		return $settings;
	}

	protected static function get_ratings()
	{
		$settings ['conditions'] = 'r.id = t.resource_id';
		$settings ['tables_add'] = ', ratings t';
		$settings ['date_column'] = 't.date';
		$settings ['select_column'] = 't.id';
		return $settings;
	}

	private static function get_settings($type)
	{
		$settings = NULL;
		switch ($type)
		{
			case "resources" :
				$settings = self::get_resources();
				break;
			case "ratings" :
				$settings = self::get_ratings();
				break;
			case "contracted" :
				$settings = self::get_contracted();
				break;
			case "suggested" :
				$settings = self::get_suggested();
				break;
			case "suggested_issn" :
				$settings = self::get_suggested('ISSN');
				break;
			case "suggested_publisher" :
				$settings = self::get_suggested('web-publisher');
				break;
			case "suggested_visitor" :
				$settings = self::get_suggested('web-visitor');
				break;
			case "catalogued" :
				$settings = self::get_catalogued();
				break;
			case "correspondence" :
				$settings = self::get_correspondence();
				break;
			case "addressed" :
				$settings = self::get_addressed();
				break;
			case "approved" :
				$settings = self::get_approved();
				break;
			default :
				throw new WaAdmin_Exception('Nesprávný typ statistik', 'Nebyl zadán správný typ statistik');
		}
		return $settings;
	}

	private static function create_date_conditions($date_column, $year = NULL, $month = NULL)
	{
		$conditions = array();
		if (! is_null($year))
		{
			$conditions [] = "YEAR({$date_column}) = {$year}";
		}
		if (! is_null($month))
		{
			$conditions [] = "MONTH({$date_column}) = {$month}";
		}
		return $conditions;
	}

	private static function combine_conditions($conditions, $operator)
	{
		$where = '';
		if (! empty($conditions))
		{
			$conditions = implode(' AND ', $conditions);
			$where = $operator." ".$conditions;
		}
		return $where;
	}
}

?>