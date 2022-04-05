<?php

	include_once 'Session.php';
	include 'Database.php';

class User
{
	private $db;
	public function __construct()
	{
		$this->db = new Database();
	}

	public function userRegistration($data)
	{
		$name     = $data['name'];
		$id_number= $data['id_number'];
		$username = $data['username'];
		$email    = $data['email'];
		$password = $data['password'];
		$chk_email = $this->emailCheck($email);
		$chk_id_number = $this->id_numberCheck($id_number);

		if ($name == ""OR $id_number == "" OR $username == "" OR $email == "" OR $password == "") 
		{
			$msg = "<div class='alert alert-danger'><strong>Error...! </strong>Field must not be empty...</div>";
			return $msg;
		}

		if (strlen($id_number) < 9)
		{
			$msg = "<div class='alert alert-danger'><strong>Error ! </strong>Invalid ID Number...</div>";
			return $msg;	
		}
		elseif (preg_match('/[^0-9]+/i',$id_number)) 
		{
			$msg = "<div class='alert alert-danger'><strong>Error ! </strong>ID Numnber must only contain numerical...!</div>";
			return $msg;	
		}

		if (strlen($username) < 3)
		{
			$msg = "<div class='alert alert-danger'><strong>Error ! </strong>Username is too short...</div>";
			return $msg;	
		}


		if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) 
		{
			$msg = "<div class='alert alert-danger'><strong>Error ! </strong>Email address is not valid...!</div>";
			return $msg;
		}

		if ($chk_email == true) {
			$msg = "<div class='alert alert-danger'><strong>Error ! </strong>Email address already exixt...!</div>";
			return $msg;
		}

		if ($chk_id_number == true) {
			$msg = "<div class='alert alert-danger'><strong>Error ! </strong>ID Numnber already exixt...!</div>";
			return $msg;
		}

		if (strlen($password) < 6)
		{
			$msg = "<div class='alert alert-danger'><strong>Error ! </strong>Password must be contain 6 character...</div>";
			return $msg;	 
		}

		$password = md5($data['password']);

		$sql = "INSERT INTO tbl_user (name, id_number, username, email, password) values (:name, :id_number, :username, :email, :password)";

		$query =  $this->db->pdo->prepare($sql);
		$query->bindValue(':name', $name);
		$query->bindValue(':id_number', $id_number);
		$query->bindValue(':username', $username);
		$query->bindValue(':email', $email);
		$query->bindValue(':password', $password);
		$result = $query->execute();

		if ($result) 
		{
			$msg = "<div class='alert alert-success'><strong> Success! </strong>You have been registered...</div>";
			return $msg;
		}
		else
		{
			$msg = "<div class='alert alert-danger'><strong>Error ! </strong>Sorry, there has been problem inserting yours details...!</div>";
			return $msg;
		}
		
	}

	public function emailCheck($email)
	{
		$sql   = "SELECT email FROM tbl_user WHERE email = :email";
		$query =  $this->db->pdo->prepare($sql);
		$query->bindValue(':email', $email);
		$query->execute();
		if ($query->rowCount() > 0) {
			return true;
		}
		else
		{
			return false;
		}
	}

	public function id_numberCheck($id_number)
	{
		$sql   = "SELECT id_number FROM tbl_user WHERE id_number = :id_number";
		$query =  $this->db->pdo->prepare($sql);
		$query->bindValue(':id_number', $id_number);
		$query->execute();
		if ($query->rowCount() > 0) {
			return true;
		}
		else
		{
			return false;
		}
	}

	public function getLoginUser($email, $password){
		$sql   = "SELECT * FROM tbl_user WHERE email = :email AND password = :password LIMIT 1";
		$query =  $this->db->pdo->prepare($sql);
		$query->bindValue(':email', $email);
		$query->bindValue(':password', $password);
		$query->execute();
		$result = $query->fetch(PDO::FETCH_OBJ);
		return $result;
	}

	public function userLogin($data){

		$email    = $data['email'];
		$password = md5($data['password']);
		
		$chk_email = $this->emailCheck($email);

		if ($email == "" OR $password == "") 
		{
			$msg = "<div class='alert alert-danger'><strong>Error...! </strong>Field must not be empty...</div>";
			return $msg;
		}

		if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) 
		{
			$msg = "<div class='alert alert-danger'><strong>Error ! </strong>Email address is not valid...!</div>";
			return $msg;
		}

		if ($chk_email == false) {
			$msg = "<div class='alert alert-danger'><strong>Error ! </strong>Email address not exist...!</div>";
			return $msg;
		}

		$result = $this->getLoginUser($email, $password);
		if ($result) {
			Session::init();
			Session::set("login",true);
			Session::set("id", $result->id);
			Session::set("name", $result->name);
			Session::set("username", $result->username);
			Session::set("loginmsg", "<div class='alert alert-success'><strong>Success ! </strong>You are loggedin...!</div>");
			header("Location: index.php");
		}else{
			$msg = "<div class='alert alert-danger'><strong>Error ! </strong>Data not found...!</div>";
			return $msg;
		}

	}

	public function getUserData(){
		$sql   = "SELECT * FROM tbl_user ORDER BY id ASC";
		$query =  $this->db->pdo->prepare($sql);
		$query->execute();
		$result = $query->fetchAll();
		return $result;
	}

	public function getUserById($userid){
		$sql   = "SELECT * FROM tbl_user WHERE id = :id LIMIT 1";
		$query =  $this->db->pdo->prepare($sql);
		$query->bindValue(':id', $userid);
		$query->execute();
		$result = $query->fetch(PDO::FETCH_OBJ);
		return $result;
	}


	public function updateUserData($id, $data){
		$name     = $data['name'];
		$id_number= $data['id_number'];
		$username = $data['username'];
		$email    = $data['email'];

		if ($name == ""OR $id_number == "" OR $username == "" OR $email == "") 
		{
			$msg = "<div class='alert alert-danger'><strong>Error...! </strong>Field must not be empty...</div>";
			return $msg;
		}

		if (strlen($id_number) < 9)
		{
			$msg = "<div class='alert alert-danger'><strong>Error ! </strong>Invalid ID Number...</div>";
			return $msg;	
		}
		elseif (preg_match('/[^0-9]+/i',$id_number)) 
		{
			$msg = "<div class='alert alert-danger'><strong>Error ! </strong>ID Numnber must only contain numerical...!</div>";
			return $msg;	
		}

		if (strlen($username) < 3)
		{
			$msg = "<div class='alert alert-danger'><strong>Error ! </strong>Username is too short...</div>";
			return $msg;	
		}


		if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) 
		{
			$msg = "<div class='alert alert-danger'><strong>Error ! </strong>Email address is not valid...!</div>";
			return $msg;
		}
		

		$sql = "UPDATE tbl_user set
					name      = :name,
					id_number = :id_number,
					username  = :username,
					email     = :email
					WHERE id  = :id";

		$query =  $this->db->pdo->prepare($sql);

		$query->bindValue(':name', $name);
		$query->bindValue(':id_number', $id_number);
		$query->bindValue(':username', $username);
		$query->bindValue(':email', $email);
		$query->bindValue(':id', $id);
		$result = $query->execute();

		if ($result) 
		{
			$msg = "<div class='alert alert-success'><strong> Success! </strong>User data updated successfully...</div>";
			return $msg;
		}
		else
		{
			$msg = "<div class='alert alert-danger'><strong>Error ! </strong>Sorry, User data not updated...!</div>";
			return $msg;
		}
	}

	public function checkPassword($id, $old_pass){
		$password = md5($old_pass);
		$sql   = "SELECT password FROM tbl_user WHERE id = :id AND password = :password";
		$query =  $this->db->pdo->prepare($sql);
		$query->bindValue(':id', $id);
		$query->bindValue(':password', $password);

		$query->execute();
		if ($query->rowCount() > 0) {
			return true;
		}
		else
		{
			return false;
		}
	}

	public function updatePassword($id, $data){
		$old_pass = $data['old_pass'];
		$new_pass = $data['password'];
		$chk_pass = $this->checkPassword($id, $old_pass);

		if ($old_pass == "" OR $new_pass == "") {
			$msg = "<div class='alert alert-danger'><strong>Error ! </strong>Sorry, Field not must be empty...!</div>";
			return $msg;
		}

		
		if ($chk_pass == false) {
			$msg = "<div class='alert alert-danger'><strong>Error ! </strong>Sorry, old password not exist...!</div>";
			return $msg;
		}
		if (strlen($new_pass) < 6) {
			$msg = "<div class='alert alert-danger'><strong>Error ! </strong>Sorry, password is too short, atleast contain 6 character...!</div>";
			return $msg;
		}

		$password = md5($new_pass);

		$sql = "UPDATE tbl_user set
					password  = :password
					WHERE id  = :id";

		$query =  $this->db->pdo->prepare($sql);

		$query->bindValue(':password', $password);
		$query->bindValue(':id', $id);
		$result = $query->execute();

		if ($result) 
		{
			$msg = "<div class='alert alert-success'><strong> Success! </strong>Password updated successfully...</div>";
			return $msg;
		}
		else
		{
			$msg = "<div class='alert alert-danger'><strong>Error ! </strong>Sorry, Password not updated...!</div>";
			return $msg;
		}
		
	}
}

?>