<?php

namespace App\Controllers;

class Home extends BaseController
{
    public function index(): string
    {
        return view('landing');
    }

    public function map(): string
    {
        return view('map_explorer');
    }

    public function nearest(): string
    {
        return view('nearest');
    }

    public function about(): string
    {
        return view('about');
    }

    public function detail(string $id): string
    {
        return view('detail', [
            'serviceCenterId' => $id,
        ]);
    }

    public function adminDashboard(): string
    {
        return view('admin_dashboard');
    }

    public function manageCenters(): string
    {
        return view('admin_manage');
    }

    public function spatialAnalysis(): string
    {
        return view('admin_analysis');
    }

    public function manageBrands(): string
    {
        return view('admin_brands');
    }

    public function manageDistricts(): string
    {
        return view('admin_districts');
    }
}
