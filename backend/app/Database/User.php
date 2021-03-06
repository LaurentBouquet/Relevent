<?php

namespace App\Database;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class User
{
    public $user_id;
    private $user_nickname;
    private $user_name;
    private $user_surname;
    private $user_mail;
    private $user_key;

    private $user_table = 'user';

    private $table_row = [
        'user_id'               => 'id',
        'user_nickname'         => 'nickname',
        'user_name'             => 'name',
        'user_surname'          => 'surname',
        'user_password'         => 'password',
        'user_mail'             => 'mail',
        'user_key'              => 'hashkey',
        'user_regitration_time' => 'regitration_time',
    ];

    /**
     * User constructor.
     *
     * @param $user_id
     */
    public function __construct($user_id)
    {
        $this->user_id = $user_id;
    }

    /**
     * @param null $user_data
     *
     * @return array
     */
    public function getUserData($user_data = null)
    {
        if ($user_data === null) {
            $user_data = DB::table($this->user_table)
                ->where($this->table_row['user_id'], $this->user_id)
                ->first();
        }

        if ($user_data !== null) {
            $this->user_nickname = $user_data->{$this->table_row['user_nickname']};
            $this->user_name = $user_data->{$this->table_row['user_name']};
            $this->user_surname = $user_data->{$this->table_row['user_surname']};
            $this->user_password = $user_data->{$this->table_row['user_password']};
            $this->user_mail = $user_data->{$this->table_row['user_mail']};
            $this->user_key = $user_data->{$this->table_row['user_key']};

            return $this->userDbToArray();
        }
    }

    /**
     * @param $nickname
     *
     * @return bool
     */
    public function userNickNameExists($nickname)
    {
        $query = DB::table($this->user_table)
            ->where($this->table_row['user_nickname'], $nickname);

        return ($query->first() === null) ? false : true;
    }

    /**
     * @param $mail
     *
     * @return bool
     */
    public function userMailExists($mail)
    {
        $query = DB::table($this->user_table)
            ->where($this->table_row['user_mail'], $mail);

        return ($query->first() === null) ? false : true;
    }

    /**
     * @param $key
     *
     * @return bool|int
     */
    public function userKeyExists($key)
    {
        $result = DB::table($this->user_table)
            ->where($this->table_row['user_key'], $key)
            ->first();

        return ($result === null) ? false : $result->{$this->table_row['user_id']};
    }

    /**
     * @param $userArray
     *
     * @return string
     */
    public function userCreate($userArray)
    {
        $key = $this->generateUserKey($userArray['nickname']);
        // return new user_id
        DB::table($this->user_table)->insert([
            $this->table_row['user_key']                => $key,
            $this->table_row['user_nickname']           => $userArray['nickname'],
            $this->table_row['user_name']               => '',
            $this->table_row['user_surname']            => '',
            $this->table_row['user_password']           => Crypt::encrypt($userArray['password']),
            $this->table_row['user_mail']               => $userArray['mail'],
            $this->table_row['user_regitration_time']   => time(),
        ]);

        return $key;
    }

    /**
     * @param $user
     *
     * @return mixed
     *               TODO : check if unique
     */
    private function generateUserKey($user)
    {
        return Hash::make($user.time());
    }

    /**
     * @param $nickname
     * @param $password
     *
     * @return array|bool
     */
    public function tryLogin($nickname, $password)
    {
        $data = DB::table($this->user_table)
            ->where($this->table_row['user_nickname'], $nickname)
            ->first();

        if ($data !== null && Crypt::decrypt($data->{$this->table_row['user_password']}) == $password) {
            return $this->getUserData($data);
        }

        return false;
    }

    /**
     * @return array
     */
    public function userDbToArray()
    {
        return [
            'nickname'         => $this->user_nickname,
            'name'             => $this->user_name,
            'surname'          => $this->user_surname,
            'mail'             => $this->user_mail,
            'key'              => $this->user_key,
        ];
    }
}
