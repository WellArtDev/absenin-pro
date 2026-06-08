<?php

require_once __DIR__ . '/../helpers/Response.php';
require_once __DIR__ . '/../helpers/Validator.php';
require_once __DIR__ . '/../models/Employee.php';

class EmployeeController
{
    private string $tenantId;
    private Employee $model;

    public function __construct(string $tenantId)
    {
        $this->tenantId = $tenantId;
        $this->model = new Employee($tenantId);
    }

    public function index(): void
    {
        ['page' => $page, 'limit' => $limit] = Request::getPagination();
        $search = Request::getQuery('search', 'string', '');

        $result = $this->model->findAll($page, $limit, $search);
        Response::success($result['data'], $result['meta']);
    }

    public function show(string $id): void
    {
        $id = Sanitizer::uuid($id);
        if (empty($id)) {
            Response::notFound('Karyawan tidak ditemukan');
        }

        $employee = $this->model->findById($id);
        if (!$employee) {
            Response::notFound('Karyawan tidak ditemukan');
        }
        Response::success($employee);
    }

    public function store(): void
    {
        $input = Request::getJson();

        $validator = new Validator($input);
        $validator->required('employee_code', 'name', 'email', 'phone', 'ktp_number', 'npwp_number', 'birth_date', 'birth_place', 'address', 'emergency_contact_name', 'emergency_contact_phone', 'division_id', 'position_id', 'employment_status', 'join_date');
        $validator->email('email');
        $validator->date('birth_date');
        $validator->date('join_date');

        $data = $validator->validate();

        if ($this->model->findByCode($data['employee_code'])) {
            Response::validationError('Kode karyawan sudah digunakan');
        }

        $id = $this->model->create($data);
        Response::created(['id' => $id]);
    }

    public function update(string $id): void
    {
        $id = Sanitizer::uuid($id);
        $input = Request::getJson();
        $validator = new Validator($input);

        if (isset($input['email'])) $validator->email('email');
        if (isset($input['birth_date'])) $validator->date('birth_date');

        $data = $validator->validate();
        $this->model->edit($id, $data);
        Response::success(['id' => $id]);
    }

    public function destroy(string $id): void
    {
        $this->model->deactivate($id);
        Response::success(['id' => $id]);
    }

    public function import(): void
    {
        if (!isset($_FILES['file'])) {
            Response::validationError('File CSV wajib diupload');
        }

        $file = $_FILES['file'];
        if ($file['error'] !== UPLOAD_ERR_OK) {
            Response::validationError('Gagal upload file');
        }

        $handle = fopen($file['tmp_name'], 'r');
        $headers = fgetcsv($handle);
        $rows = [];

        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) === count($headers)) {
                $rows[] = array_combine($headers, $row);
            }
        }
        fclose($handle);

        $result = $this->model->importBatch($rows);
        Response::success($result);
    }
}
