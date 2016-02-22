<?php

namespace Manager;

use Model\InitConsts as IC;

class DatabaseManager implements IC
{
    /**
     * @var
     */
    protected $sqli;

    /**
     * @var
     */
    public $dateOrder;

    /**
     * DatabaseManager constructor.
     */
    public function __construct()
    {
        $this->sqli = new \mysqli(IC::MYSQLI_HOST, IC::MYSQLI_USER, IC::MYSQLI_PASSWORD, IC::MYSQLI_DBNAME);
        $this->sqli->set_charset("utf8");
        var_dump($this->sqli->character_set_name());
        
        $this->dateOrder = date('Y-m-d H:i:s');
    }

    /**
     * @param $p1_email
     * @param null $p2_password
     * @return array|string
     */
    public function fetchUser($p1_email, $p2_password = NULL)
    {
        if(NULL !== $p2_password)
        {
            $query = 'SELECT id, email, password
                      FROM tbl_customers
                      WHERE TRIM(email) = "'.$this->sqli->real_escape_string($p1_email).'"
                      AND password = "'.sha1($this->sqli->real_escape_string($p2_password)).'"';

            $resultFetchMail = $this->sqli->query($query);

            if(is_object($resultFetchMail))
            {
                $numRows = $resultFetchMail->num_rows;

                if ($numRows === 1)
                {
                    return $resultFetchMail->fetch_assoc();     //returning array here

                }elseif($numRows === 0)
                {
                    return WRONG_CREDENTIALS;

                }elseif($numRows > 1) //really few probabilities it happens
                {
                    return DUPLICATED_MAILS_IN_DB;
                }

            }else return $this->sqli->error;

        }else{

            $query = 'SELECT email, password
                      FROM tbl_customers
                      WHERE TRIM(email) = "'.$this->sqli->real_escape_string($p1_email).'"';

            $resultFetchMail = $this->sqli->query($query);

            if(is_object($resultFetchMail))
            {
                if ($resultFetchMail->num_rows === 1)
                {
                    $row = $resultFetchMail->fetch_array();

                    if($row['password'] === IC::HASH_PASSWD) //same initial hash for everyone at the beginning
                    {
                        return FALSE;

                    }elseif($row['password'] !== IC::HASH_PASSWD)
                    {
                        return TRUE;        // most frequent moment where user exists with other passwd than PSK
                    }

                }elseif($resultFetchMail->num_rows > 1)
                {
                    return DUPLICATED_MAILS_IN_DB;

                }elseif($resultFetchMail->num_rows === 0)
                {
                    return NO_INSCRIPTIONS_ALLOWED;
                }

            }else return $this->sqli->error;
        }
    }

    /**
     * @param array $a
     * @return bool|string
     */
    public function updateUserPassword(array $a)
    {
        if(strlen($a['password']) > 5)
        {
            if(sha1($a['password']) !== IC::HASH_PASSWD)
            {
                $output = $this->fetchUser($a['email']);

                if(TRUE === $output)
                {
                    $query = 'UPDATE tbl_customers
                              SET password = "'.sha1($this->sqli->real_escape_string($a['password'])).'"
                              WHERE TRIM(email) = "'.$this->sqli->real_escape_string($a['email']).'"';

                    $result = $this->sqli->query($query);

                    if($result)
                    {
                        $affectedRows = $this->sqli->affected_rows;

                        if($affectedRows > 1)
                        {
                            return DUPLICATED_MAILS_IN_DB;

                        }else return TRUE;

                    }else return $this->sqli->error;

                }else return (FALSE === $output) ? MUST_DO_A_FIRST_LOGIN : $output;

            }else return DEFINE_NEW_PASSWD;

        }else return MIN_LEN_PASSWD;
    }

    /**
     * @param $p1_item
     * @param bool $p2_display_only_available
     * @param bool $p3_display_active
     * @return \Generator
     */
    public function fetchItemInfos($p1_item, $p2_display_only_available = TRUE, $p3_display_active = TRUE)
    {
        $sql = 'SELECT * FROM tbl_'.$p1_item.' ';

        if($p2_display_only_available && $p3_display_active)
        {
            $sql .= 'WHERE quantity > 0 AND active = 1';

        }elseif($p2_display_only_available XOR $p3_display_active)
        {
            $sql .= $p2_display_only_available ? ' WHERE quantity > 0' : 'WHERE active = 1';
        }
        
        $result = $this->sqli->query($sql);

        if(is_object($result))
        {
            if ($result->num_rows > 0)
            {
                while ($rows = $result->fetch_array()) yield $rows;

            }else yield NO_ITEM_FOUND_IN_DB;

        }else yield $this->sqli->error;
    }

    /**
     * @param array $p1_datas_post
     * @param $p2_filesSaved
     * @param $p3_id_customer
     * @return bool|string
     */
    public function saveOrder(array $p1_datas_post, $p2_filesSaved, $p3_id_customer)
    {
        $queryOne = 'INSERT INTO tbl_orders
                     SET id_customer = '.$p3_id_customer.',
                     date_order = "'.$this->dateOrder.'",
                     total = "'.$this->sqli->real_escape_string($p1_datas_post['total']).'",
                     status = '.$p2_filesSaved.',
                     item = "'.$p1_datas_post['item'].'"';

        $resultOne = $this->sqli->query($queryOne);

        if($resultOne)
        {
            $insertIdQueryOne = $this->sqli->insert_id;

            $onlyTampoonInfos = [];

            foreach($p1_datas_post as $k => $v):

                if(FALSE !== stripos($k, '_'))      //the post key that has underscore correspond to tampoon ref
                {
                    $tampoonRef = strtr($k, '_', ' ');
                    $onlyTampoonInfos[$tampoonRef] = $v;

                    $queryTwo = 'INSERT INTO tbl_orders_details
                                 SET id_order = '.$insertIdQueryOne.',
                                  id_item = (SELECT id FROM tbl_'.$p1_datas_post['item'].' WHERE tbl_'.$p1_datas_post['item'].'.reference = "'.$tampoonRef.'"),
                                  quantity = '.(int)$v;

                    $resultTwo = $this->sqli->query($queryTwo);

                    if(!$resultTwo) return $this->sqli->error;
                }

            endforeach;

            return $this->updateItemQuantities($onlyTampoonInfos, $p1_datas_post['item']);

        }else return $this->sqli->error;

    }

    /**
     * @param array $p1_tampoon_infos
     * @param $p2_item
     * @return bool|string
     */
    public function updateItemQuantities(array $p1_tampoon_infos, $p2_item)
    {
        foreach($p1_tampoon_infos as $k => $v):

            $query = 'UPDATE tbl_'.$p2_item.' AS tp1 INNER JOIN tbl_'.$p2_item.' AS tp2 ON tp1.reference = tp2.reference AND tp1.reference = "'.$k.'" SET tp1.quantity = (tp2.quantity - '.(int)$v.')';

            $result = $this->sqli->query($query);

            if($result){

                mysqli_free_result($result); //don't remove this line

            }else return $this->sqli->error;

        endforeach;

        return TRUE;
    }

    /**
     * @param array $datasPost
     * @return array|string
     */
    public function updatePasswdAndlogin(array $datasPost) //for first login only
    {

        $query = 'UPDATE tbl_customers
                  SET password = "'.sha1($this->sqli->real_escape_string($datasPost['new_password'])).'"
                  WHERE TRIM(email) = "'.$this->sqli->real_escape_string($datasPost['email']).'"
                  AND password = "'.IC::HASH_PASSWD.'"';

        $result = $this->sqli->query($query);

        if($result)
        {
            $affectedRows = $this->sqli->affected_rows;

            if ($affectedRows === 1)
            {
                return $this->fetchUser($datasPost['email'], $datasPost['new_password']);

            }elseif($affectedRows === 0)
            {
                return UNEXISTING_EMAIL;

            }elseif($affectedRows > 1)
            {
                return DUPLICATED_MAILS_IN_DB;
            }

        }else return $this->sqli->error;
    }
}
