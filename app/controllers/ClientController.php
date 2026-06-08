<?php

require_once __DIR__ . '/../helpers/Response.php';
require_once __DIR__ . '/../models/ClientModel.php';

class ClientController
{
    private string $tenantId;
    private ClientModel $model;

    public function __construct(string $tenantId)
    {
        $this->tenantId = $tenantId;
        $this->model = new ClientModel($tenantId);
    }

    public function index(): void
    {
        $clients = $this->model->findAll();
        Response::success($clients);
    }

    public function store(): void
    {
        $input = json_decode(file_get_contents('php://input'), true);

        if (empty($input['name']) || !isset($input['gps_lat']) || !isset($input['gps_lng'])) {
            Response::validationError('Nama, gps_lat, dan gps_lng wajib');
        }

        $id = $this->model->create($input);
        Response::created(['id' => $id]);
    }

    public function update(string $id): void
    {
        $input = json_decode(file_get_contents('php://input'), true);
        $this->model->edit($id, $input);
        Response::success(['id' => $id]);
    }

    public function destroy(string $id): void
    {
        $this->model->remove($id);
        Response::success(['id' => $id]);
    }
}
