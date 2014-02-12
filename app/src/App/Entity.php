<?php
namespace App;
use Spot;

class Entity extends Spot\Entity
{
    protected static $_formFields = array();
    protected static $_formFieldOptions = array();

    /**
     * Return only field info that we want exposed in API 'fields'
     */
    public static function formFields()
    {
        $fields = array_intersect_key(static::fields(), array_flip(static::$_formFields));
        $output = array();

        foreach($fields as $field => $arr) {
            $arr['name'] = $field;
            if(isset(static::$_formFieldOptions[$field])) {
                $arr = array_merge($arr, static::$_formFieldOptions[$field]);
            }
            $output[] = $arr;
        }

        return $output;
    }

    /**
     *
     */
    public function formFieldsWithValues()
    {
        $fields = self::formFields();
        foreach($fields as $index => $fieldArray) {
            $field = $fieldArray['name'];
            $value = $this->$field;

            if($value instanceof \DateTime) {
                $value = $value->format('c');
            }

            $fields[$index]['value'] = $value;

            if(isset(self::$_formFieldOptions[$field])) {
                $fields[$index] = array_merge($fields[$index], self::$_formFieldOptions[$field]);
            }
        }
        return $fields;
    }

    /**
     * Return array of field data with ONLY data from the field names listed
     *
     * @param array List of field names to include in data list returned
     */
    public function dataOnly(array $only)
    {
        return array_intersect_key($this->data(), array_flip($only));
    }
}

