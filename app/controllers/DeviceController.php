<?php

require_once __DIR__ . '/../helpers/Response.php';
require_once __DIR__ . '/../models/Device.php';

class DeviceController
{
    private string $tenantId;
    private Device $model;

    public function __construct(string $tenantId)
    {
        $this->tenantId = $tenantId;
        $this->model = new Device($tenantId);
    }

    public function register(): void
    {
        $input = Request::getJson();
        $employeeId = $input['employee_id'] ?? '';
        $deviceId = $input['device_id'] ?? '';
        $platform = $input['platform'] ?? 'android';

        if (empty($employeeId) || empty($deviceId)) {
            Response::validationError('Employee ID dan Device ID wajib');
        }

        $id = $this->model->register($employeeId, $deviceId, $platform);
        Response::success(['device_id' => $id]);
    }
}
