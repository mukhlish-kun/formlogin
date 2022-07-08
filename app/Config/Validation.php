<?php namespace Config;

class Validation
{
	//--------------------------------------------------------------------
	// Setup
	//--------------------------------------------------------------------

	/**
	 * Stores the classes that contain the
	 * rules that are available.
	 *
	 * @var array
	 */
	public $ruleSets = [
		\CodeIgniter\Validation\Rules::class,
		\CodeIgniter\Validation\FormatRules::class,
		\CodeIgniter\Validation\FileRules::class,
		\CodeIgniter\Validation\CreditCardRules::class,
	];

	/**
	 * Specifies the views that are used to display the
	 * errors.
	 *
	 * @var array
	 */
	public $templates = [
		'list'   => 'CodeIgniter\Validation\Views\list',
		'single' => 'CodeIgniter\Validation\Views\single',
	];

	//--------------------------------------------------------------------
	// Rules
	//--------------------------------------------------------------------
	public $users = [
		'name' => 'required',
        'username'     => 'required|min_length[4]',
        'password'     => 'required',
        'pass_confirm' => 'required|matches[password]',
        'email'        => 'required|valid_email'
	];
	
	public $users_errors = [
		'name'=> [
			'required'  => 'Nama harus diisi.'
		],
		'username'=> [
			'min_length[4]'  => 'Nama minimal 4 huruf.'
		],
		'password'=> [
			'required'  => 'Password harus terdiri dari angka dan huruf.'
		],
		'pass_confirm'=> [
			'matches[password]'  => 'Konfirmasi password tidak cocok.'
		],
		'email'=> [
			'valid_email'  => 'format email salah.'
		],
	];
}
