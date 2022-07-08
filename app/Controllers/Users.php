<?php namespace App\Controllers;
 
use CodeIgniter\RESTful\ResourceController;
use \Firebase\JWT\JWT;
use App\Models\Users_model;
 
class Users extends ResourceController
    {
        protected $format       = 'json';
        protected $modelName    = 'App\Models\Users_model';

        public function __construct()
        {
            $this->model = new Users_model();
        }

        public function privateKey()
        {
            $privateKey = "aezakmikmzway87aa";
            return $privateKey;
        }
 
        public function index()
        {
            return $this->respond($this->model->findAll(), 200);
        }

        public function create()
        {
            $validation =  \Config\Services::validation();
 
            $name   = $this->request->getPost('name');
            $username   = $this->request->getPost('username');
            $password   = $this->request->getPost('password');
            $pass_confirm = $this->request->getPost('pass_confirm');
            $email   = $this->request->getPost('email');
            
            $data = [
                'name' => $name,
                'username' => $username,
                'password' => $password,
                'pass_confirm' => $pass_confirm,
                'email' => $email,
            ];
     
            if($validation->run($data, 'users') == FALSE){
                $response = [
                    'status' => 500,
                    'error' => true,
                    'data' => $validation->getErrors(),
                ];
                return $this->respond($response, 500);
            } else {
                array_splice($data, 3, 3);
                $password_hash = password_hash($password, PASSWORD_BCRYPT);
                $data['password'] = $password_hash;
                $simpan = $this->model->insertUsers($data);
                if($simpan){
                    $msg = ['message' => 'User telah diregister'];
                    $response = [
                        'status' => 200,
                        'error' => false,
                        'data' => $msg,
                    ];
                    return $this->respond($response, 200);
                }
            }
        }

        public function update($id = NULL)
  {
		try {
			$secret_key = $this->privateKey();

			// ini ngambil Authorization Header nya, sekalian dicek ada apa nggak
			$authHeader = $this->request->getServer('HTTP_AUTHORIZATION');
			if ($authHeader == NULL) throw new \Exception("Missing Authorization Header");

			// Kan bentuk Authorization Headernya 
			// Bearer {token}
			// nah untuk ngambil tokennya bisa kayak gini
			$arr = explode(" ", $authHeader);
	        $token = $arr[1];
			
			// Ini ngecek token nya ada apa nggak
			// untuk kasus Auth Header yang isinya 'Bearer '
			if($token){
				// Ini data di tokennya di decode sekalian di cek
				// Kalo error nanti langsung ngeluarin exception
				// Terus di tangkap geh sama yang Catch dibawah
				$decoded = JWT::decode($token, $secret_key, array('HS256'));
				
				// Kalo berhasil di decode ya di balikin geh
				if($decoded){
					$validation =  \Config\Services::validation();
                    $data = $this->request->getRawInput();
                    $decoded_array = (array) $decoded;
                    $decode = (array) $decoded_array['data'];
                    $id = $decode['id'];
                    if($validation->run($data, 'users') == FALSE){
                        $response = [
                            'status' => 500,
                            'error' => true,
                            'data' => $validation->getErrors(),
                        ];
                        return $this->respond($response, 500);
                    } else {
                        array_splice($data, 4, 4);
                        $password = $data['password'];
                        $password_hash = password_hash($password, PASSWORD_BCRYPT);
                        $data['password'] = $password_hash;
                        $simpan = $this->model->updateUsers($data,$id);
                        if($simpan){
                            $msg = ['message' => 'User telah diupdate.'];
                            $response = [
                                'status' => 200,
                                'error' => false,
                                'data' => $msg,
                            ];
                            return $this->respond($response, 200);
                        }
                    }
			    }	
			} else {
        $output = [
          'status' => 200,
          'message' => "Authorization failed",
        ];

        return $this->respond($output, 200);
      }
		} catch (\Exception $e) {

				$output = [
				  'status' => 401,
			    'message' => $e->getMessage(),
			  ];

			  return $this->respond($output, 401);
		}
  }
        public function login()
        {

            $username   = $this->request->getPost('username');
            $password   = $this->request->getPost('password');

            $cek_login = $this->model->checkLogin($username);

            if (password_verify($password, $cek_login['password'])) {

            // Disini akan dibuat konifugurasi untuk membuat tokennya
            // Lebih lengkap baca di RFC7519
            $secret_key = $this->privateKey();
            $issuer_claim = "THE_CLAIM";
            $audience_claim = "THE_AUDIENCE";
            $issuedat_claim = time(); // issued at
            $notbefore_claim = $issuedat_claim + 10;
            $expire_claim = $issuedat_claim + 3600; //ini maksudnya token expired klo udah 3600s
            $token = array(
                "iss" => $issuer_claim,
                "aud" => $audience_claim,
                "iat" => $issuedat_claim,
                "nbf" => $notbefore_claim,
                "exp" => $expire_claim,
                "data" => array(
                "id" => $cek_login['id'],
                "username" => $cek_login['username']
                )
            );

            // dari konfigurasi tadi, dibuat tokennya pake fungsi ini
            $token = JWT::encode($token, $secret_key);

            // Kalau login berhasil, tokennya jadi response nya
            $output = [
                'status' => 200,
                'message' => 'Berhasil login',
                "token" => $token,
                "username" => $username,
            ];
            return $this->respond($output, 200);
            } else {
            $output = [
                'status' => 401,
                'message' => 'Login failed',
                "password" => $password
            ];
            return $this->respond($output, 401);
            }
        }

}