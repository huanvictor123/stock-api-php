<?php

namespace Controllers;

use Models\Report;
use Helpers\Response;

class ReportController
{
    private Report $model;

    public function __construct()
    {
        $this->model = new Report();
    }

    public function lowStock(): void
    {
        $products = $this->model->lowStock();
        Response::success($products);
    }

    public function salesSummary(): void
    {
        $start = $_GET['start'] ?? null;
        $end   = $_GET['end']   ?? null;
        $summary = $this->model->salesSummary($start, $end);
        Response::success($summary);
    }
}
