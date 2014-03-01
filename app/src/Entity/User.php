<?php
namespace Entity;
use Spot;

class User extends Spot\Entity
{
    // Table
    protected static $_datasource = "users";

    /**
     * Fields
     */
    public static function fields()
    {
        return array(
            'id' => array('type' => 'int', 'primary' => true, 'serial' => true),
            'name' => array('type' => 'string', 'required' => true),
            'phone_number' => array('type' => 'string', 'required' => true, 'unique' => true),
            'tagcode' => array('type' => 'string', 'required' => true, 'unique' => true),
            'current_event_id' => array('type' => 'int'),
            'date_created' => array('type' => 'datetime', 'default' => new \DateTime())
        ) + parent::fields();
    }

    /**
     * Hooks
     */
    public static function hooks()
    {
        return [
            'beforeInsert' => ['hookVerifyPhoneNumber', 'hookGenerateTagcode']
        ];
    }

    /**
     * Verify and store phone number in E-164 format
     */
    public function hookVerifyPhoneNumber(\Spot\Mapper $mapper)
    {
        // Ensure phone number is stored in correct format
        $phoneUtil = \libphonenumber\PhoneNumberUtil::getInstance();
        try {
            $parsed = $phoneUtil->parse($this->phone_number, "US");
        } catch (\libphonenumber\NumberParseException $e) {
            $this->error('phone_number', 'Invalid phone number format');
            return;
        }

        $phone_number = $phoneUtil->format($parsed, \libphonenumber\PhoneNumberFormat::E164);
        $this->phone_number = $phone_number;
    }

    /**
     * Generate tagcode for user
     */
    public function hookGenerateTagcode(\Spot\Mapper $mapper)
    {
        if($this->tagcode !== null) { return; }

        $app = app();
        $codeBlacklist = $app['users']['tagcode_blacklist'];
        do {
            $code = strtoupper($this->randomString(3));
            $codeUsed = $app['mapper']->first('Entity\User', ['tagcode' => $code]);
        } while($codeUsed || in_array($code, $codeBlacklist));

        $this->tagcode = $code;
    }

    /**
     * Is user logged-in?
     *
     * @return boolean
     */
    public function isLoggedIn()
    {
        return $this->__get('id') ? true : false;
    }

    /**
     * Return existing salt or generate new random salt if not set
     */
    protected function randomString($length = 42)
    {
        $string = "";
        $possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $possibleLen = strlen($possible);

        for($i=0;$i < $length;$i++) {
            $char = $possible[mt_rand(0, $possibleLen-1)];
            $string .= $char;
        }

        return $string;
    }

    /**
     * Array output for json_encode
     */
    public function toArray()
    {
        return array(
            'properties' => array_merge(
                array(
                   'date_created' => ($this->date_created) ? $this->date_created->format('Y-m-d') : null
                )
            ),
        );
    }
}

