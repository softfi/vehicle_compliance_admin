<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Models\AdminModel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Reader\Csv;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use DateTimeImmutable;
use DateTime;

class Home extends BaseController
{
    	public function __construct()
	{
		$db = db_connect();
		$this->db = db_connect();

		$this->AdminModel = new AdminModel($db);
		$this->session = session();
		helper(['form', 'url', 'validation']);
		
	}
	
    public function index(): string
    {
       	return view('admin/login');
    }
}
