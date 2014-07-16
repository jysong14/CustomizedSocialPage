<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class Profile_Model extends Model {

    function __construct() {
        parent::__construct();
    }
    
    public function check_user($username)
    {
        $statement = $this->db->select(array("id"), "users", array("login"), array($username));
        if(!$statement){
            throw new Exception('Query failed.');
        }
        
        if ($statement->rowCount() > 0)
        {
            return true;
        }
        
        return false;
    }
    
    public function post($from, $type)
    {
        if(!isset($from) || empty($from))
        {
            $statement = $this->db->insert("status", array("UId", "Status"), array(Session::get('userId'), $_POST['post-text']));
            
            if ($statement->rowCount() > 0)
            {
                $statusId = $this->db->lastInsertId();
                $statement = $this->db->insert("wall", array("whereId", "StatusId", "Type"), array(Session::get('userId'), $statusId, $type));
            }
            else
            {
                echo "Network Connection fails";
                exit;
            }
        }
        
        
        if ($statement->rowCount() > 0)
        {
            // if from is from profile page
            header('location: ' . '../' . Session::get('username'));
            // otherwise,
        }
        else
        {
            echo "Network Connection fails";
            exit;
        }
    }

    public function get_status()
    {
        $result = array();
        
        //////////// Db connection goes here ////////////
        $statement = $this->db->prepare("Select users.login, table1.status
                                        From (
                                            Select status.status, status.UId
                                                From wall
                                                Inner join users
                                                    On users.Id = wall.whereId
                                                Inner join status
                                                    On wall.StatusId = status.Id ) table1
                                            Inner join users
                                                On users.Id = table1.UId
                                                ");
        $success = $statement->execute();
        if($success)
        {
            $query = $statement->fetchAll();
            
            foreach ($query as $row)
            {
                array_push($result, $this->formatter($row['login'], $row['status']));
            }
        }
        else
        {
            echo 'error';
            exit;
        }
        
	return $result;
    }
    
    /**
     * 
     * @param string $writer    Name of writer
     * @param string $post      Post context
     * @param array $commentors List of commentors    
     * @param array $comments   List of comments
     */
    private function formatter($writer, $post, $commentors = null, $comments = null)
    {
        if (count($commentors) != count($comments))
        {
            return NULL;
        }
        
        $result = '{
                        "Writer": "' . $writer. '",
                        "Post": "' . $post . '",
                        "Comments": 
                        [';
                            for ($i=0; $i<count($commentors); $i++)
                            {
                                $result .= '{"Writer": "' . $commentors[$i]. '", "Comment": "' . $comments[$i] . '"}';
                                if ($i+1 != count($commentors))
                                {
                                    $result .= ', ';
                                }
                            }
        $result .=      ']
                    }';
        
        return json_decode($result, true);
    }
}