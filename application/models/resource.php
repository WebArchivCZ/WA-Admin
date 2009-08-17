<?php
defined('SYSPATH') or die('No direct script access.');
/**
 * Model representing resource
 *
 */
class Resource_Model extends Table_Model
{

    protected $primary_val = 'title';
    protected $sorting = array('title' => 'asc');

    public $headers = array(
    'title',
    'url',
    'publisher'
    );

    protected $belongs_to = array(
    'contact' ,
    'creator' => 'curator',
    'curator' => 'curator',
    'publisher' ,
    'contract' ,
    'conspectus' ,
    'crawl_freq' ,
    'resource_status' ,
    'suggested_by');

    protected $has_many = array(
    'seeds' ,
    'ratings' ,
    'correspondence' ,
    'quality_control');

    public function __construct ($id = NULL)
    {
        parent::__construct($id);
        if (is_null($id))
        {
            $date_format = Kohana::config('wadmin.date_format');
            $this->date = date($date_format);
        }
    }

    // FIXME opravit nastavovani data
    public function __set ($key, $value)
    {
        if ($key === 'catalogued' AND $value == TRUE)
        {
            $date_format = Kohana::config('wadmin.date_format');
            $value = date($date_format);
        }
        if ($key == 'date')
        {
            $date = new DateTime($value);
            $value = $date->format(DATE_ATOM);
        }
        parent::__set($key, $value);
    }

    public function __get ($column)
    {
        $value = parent::__get($column);
        if ($column === 'date' OR $column === 'catalogued')
        {
            if ( ! is_null($value))
            {
                return date_helper::short_date($value);
            }
        }
        // TODO prepracovat data v databazi a tahat je z DB
        if ($column === 'rating_result')
        {
            $value = $this->compute_rating();
        }
        return $value;
    }

    public function is_related ($column)
    {
    // TODO prepsat natvrdo napsaneho kuratora, ktery zdroj vlozil
        return in_array($column, $this->belongs_to) or $column == 'creator';
    }

    /**
     * Rozhoduje, zda zdroj spravuje dany kurator
     * @param Curator_Model $curator
     * @return bool pravda pokud kurator spravuje zdroj
     */
    public function is_curated_by ($curator)
    {
        if ($curator instanceof Curator_Model AND $curator->__isset('id'))
        {
            if ($this->curator_id == $curator->id)
            {
                return TRUE;
            } else
            {
                return FALSE;
            }
        } else
        {
            throw new InvalidArgumentException('Predany argument neni kurator');
        }
    }

    public function add_curator ($curator)
    {
        if ($curator instanceof Curator_Model)
        {
            $this->curator_id = $curator->id;
        } else
        {
            throw new InvalidArgumentException();
        }
    }

    public function add_publisher ($publisher)
    {
        if ($publisher instanceof Publisher_Model)
        {
            $this->publisher_id = $publisher->id;
        } else
        {
            throw new InvalidArgumentException();
        }
    }

    /**
     * Funkce vraci korespondenci daneho typu, ktera je vedena k danemu zdroji
     */
    public function get_correspondence ($type)
    {
        $correspondence = ORM::factory('correspondence')
            ->where(array('resource_id' => $this->id, 'correspondence_type_id' => $type))
            ->find();
        return $correspondence;
    }

    /**
     * Funkce vraci datum posledniho kontaktaktovani vydavatele zdroje
     * return date datum posledniho kontaktu
     */
    public function get_last_contact() {
        $correspondence = ORM::factory('correspondence')
                            ->where('resource_id', $this->id)
                            ->orderby('date', 'DESC')
                            ->find();
        if ($correspondence->date != '') {
            return date_helper::short_date($correspondence->date);
        } else {
            return 'Nekontaktován';
        }
    }

    /**
     * Pokud je jiz zaznam finalniho hodnoceni v databazi (rating_result sloupec), pak je vracena tato hodnota.
     * V opacnem pripade je hodnoceni spocitano z hodnoceni jednotlivych kuratoru.
     * @param int $round
     * @param String $return_type
     * @return int
     */
    public function compute_rating($round = 1, $return_type = 'string')
    {
    // TODO rozhodnout jestli vracet INT nebo rovnou hodnoceni
    //$ratings_result = Kohana::config('wadmin.ratings_result');
    // FIXME zjistit hodnoceni daneho kola
        $value = parent::__get('rating_result');
        if ($value == '')
        {
            $ratings = ORM::factory('rating')->where(array('resource_id'=> $this->id))->find_all();
            if ($ratings->count() == 0) {
                return FALSE;
            }
            $result = 0;
            foreach ($ratings as $rating)
            {
                $rating = $rating->rating;
                if ($rating == 4)
                {
                    $final_rating = $rating;
                }
                $result += $rating;
            }
            $result = $result / $ratings->count();
            if ($result < 0.5)
            {
                $final_rating = 1;
            } elseif ($result >= 0.5 AND $rating < 1)
            {
                $final_rating = 3;
            } else
            {
                $final_rating = 2;
            }
        } else
        {
            $final_rating = $value;
        }
        if ($return_type == 'string') {
            $values = Rating_Model::get_final_array();
            return $values[$final_rating];
        } else {
            return $final_rating;
        }
    }

    public function rating_count($round = 1)
    {
        $ratings = ORM::factory('rating')->where(array('resource_id'=> $this->id,
            'round' => $round))
            ->find_all();
        return $ratings->count();
    }

}
?>