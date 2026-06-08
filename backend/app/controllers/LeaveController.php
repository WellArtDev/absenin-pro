<?php

require_once __DIR__ . '/../helpers/Response.php';
require_once __DIR__ . '/../helpers/Validator.php';
require_once __DIR__ . '/../models/LeaveRequest.php';

class LeaveController
{
    private string $tenantId;
    private LeaveRequest $model;

    public function __construct(string $tenantId)
    {
        $this->tenantId = $tenantId;
        $this->model = new LeaveRequest($tenantId);
    }

    public function index(): void
    {
        ['page' => $page, 'limit' => $limit] = Request::getPagination();
        $status = Request::getQuery('status', 'string', '');

        $result = $this->model->findAll($page, $limit, $status);
        Response::success($result['data'], $result['meta']);
    }

    public function store(): void
    {
        $input = Request::getJson();

        $validator = new Validator($input);
        $validator->required('employee_id', 'leave_type', 'start_date', 'end_date', 'reason');
        $validator->in('leave_type', [LEAVE_ANNUAL, LEAVE_PERMISSION, LEAVE_SICK]);
        $validator->date('start_date');
        $validator->date('end_date');

        $data = $validator->validate();

        if ($data['end_date'] < $data['start_date']) {
            Response::validationError('Tanggal selesai tidak boleh sebelum tanggal mulai');
        }

        $id = $this->model->create($data);
        Response::created(['id' => $id]);
    }

    public function approve(string $id): void
    {
        $input = Request::getJson();
        $approvedBy = $input['approved_by'] ?? 'system';

        if (!$this->model->approve($id, $approvedBy)) {
            Response::notFound('Pengajuan tidak ditemukan atau sudah diproses');
        }
        Response::success(['approved' => true]);
    }

    public function reject(string $id): void
    {
        $input = Request::getJson();
        $reason = $input['reason'] ?? '';

        if (empty($reason)) {
            Response::validationError('Alasan penolakan wajib diisi');
        }

        if (!$this->model->reject($id, $reason)) {
            Response::notFound('Pengajuan tidak ditemukan atau sudah diproses');
        }
        Response::success(['rejected' => true]);
    }

    public function conflicts(): void
    {
        $employeeId = Request::getQuery('employee_id', 'uuid', '');
        $start = Request::getQuery('start_date', 'date', '');
        $end = Request::getQuery('end_date', 'date', '');

        if (empty($employeeId) || empty($start) || empty($end)) {
            Response::validationError('employee_id, start_date, dan end_date wajib');
        }

        $conflicts = $this->model->findConflicts($employeeId, $start, $end);
        Response::success($conflicts);
    }
}
