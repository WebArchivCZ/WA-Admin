<?php
abstract class Table_Model extends ORM
{

    public $headers;

    public function table_columns ()
    {
        $columns = array();
        foreach ($this->headers as $header)
        {
            if (in_array($header, $this->belongs_to))
            {
                $column = new Column_Item($header, $header . '_id', TRUE);
                $column->link = TRUE;
            } else
            {
                $column = new Column_Item($header);
                if ($header == $this->primary_val or $header == $this->primary_key) {
                    $column->link = TRUE;
                }
            }
            array_push($columns, $column);
        }
        return $columns;
    }

    public function __toString ()
    {
        return $this->{$this->primary_val};
    }

    public function is_related ($column)
    {
        return (boolean) in_array($column, $belongs_to);
    }

    /**
     *
     */
    public function find_insert ($default_value, $values = NULL)
    {
        $column = $this->primary_val;
        $model = ORM::factory($this->object_name)->where($column, $default_value)->find();

        if (empty($model->{$column}))
        {
            $model->{$column} = $default_value;
            if ( ! is_null($values))
            {
                foreach ($values as $key => $value)
                {
                    $model->{$key} = $value;
                }
            }
            return $model->save();
        } else
        {
            return $model;
        }
    }

    public function unique_key($id = NULL)
    {
        if ( ! empty($id) AND is_string($id) AND ! ctype_digit($id) )
        {
            return $this->primary_val;
        }

        return parent::unique_key($id);
    }

//    public function __get ($column) {
//        if ($this->is_related($column)) {
//            return $this->{$column}->primary_val
//        } else {
//            return parent::__get($column);
//        }
//    }
}
?>