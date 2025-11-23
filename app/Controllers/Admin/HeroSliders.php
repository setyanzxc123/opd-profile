<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Services\HeroSliderService;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\Exceptions\PageNotFoundException;
use Config\HeroSlider as HeroSliderConfig;

/**
 * Hero Sliders Controller
 * 
 * Mengelola CRUD hero slider untuk website OPD
 * Controller ini thin - business logic ada di HeroSliderService
 */
class HeroSliders extends BaseController
{
    protected HeroSliderService $service;
    protected HeroSliderConfig $config;

    public function __construct()
    {
        $this->service = new HeroSliderService();
        $this->config = config('HeroSlider');
    }

    /**
     * Display list of sliders
     */
    public function index(): string
    {
        // Auto-create default sliders if empty
        $result = $this->service->ensureDefaultSliders();
        
        // Show notification if sliders were auto-created
        if ($result['success'] && $result['created'] > 0) {
            session()->setFlashdata('success', $result['message']);
        }

        $paginatedData = $this->service->getPaginatedSliders();

        $data = [
            'title'      => 'Kelola Hero Slider',
            'section'    => 'hero-sliders',
            'sliders'    => $paginatedData['sliders'],
            'pager'      => $paginatedData['pager'],
            'maxSlots'   => $this->config->maxSlots,
            'remaining'  => $this->service->getRemainingSlots(),
            'canAddMore' => $this->service->canAddMoreSliders(),
            'config'     => $this->config,
        ];

        return view('admin/hero_sliders/index', $data);
    }

    /**
     * Show create form
     */
    public function create(): string|RedirectResponse
    {
        // Check if we can add more sliders
        if (!$this->service->canAddMoreSliders()) {
            return redirect()
                ->back()
                ->with('errors', ["Slot maksimum ({$this->config->maxSlots}) sudah tercapai."]);
        }

        $data = [
            'title'      => 'Tambah Hero Slider',
            'section'    => 'hero-sliders',
            'newsItems'  => $this->service->getNewsOptions(),
            'validation' => \Config\Services::validation(),
            'config'     => $this->config,
        ];

        return view('admin/hero_sliders/form', $data);
    }

    /**
     * Store new slider
     */
    public function store(): RedirectResponse
    {
        // Get form data
        $postData = $this->request->getPost();
        $imageFile = $this->request->getFile('image_file');

        // Create slider via service
        $result = $this->service->createSlider($postData, $imageFile);

        if ($result['success']) {
            return redirect()
                ->to('/admin/hero-sliders')
                ->with('success', $result['message']);
        }

        return redirect()
            ->back()
            ->withInput()
            ->with('errors', [$result['message']]);
    }

    /**
     * Show edit form
     */
    public function edit(int $id): string
    {
        $slider = $this->service->getSliderById($id);

        if (!$slider) {
            throw PageNotFoundException::forPageNotFound();
        }

        $data = [
            'title'      => 'Edit Hero Slider',
            'section'    => 'hero-sliders',
            'slider'     => $slider,
            'newsItems'  => $this->service->getNewsOptions(),
            'validation' => \Config\Services::validation(),
            'config'     => $this->config,
        ];

        return view('admin/hero_sliders/form', $data);
    }

    /**
     * Update existing slider
     */
    public function update(int $id): RedirectResponse
    {
        // Get form data
        $postData = $this->request->getPost();
        $imageFile = $this->request->getFile('image_file');

        // Update slider via service
        $result = $this->service->updateSlider($id, $postData, $imageFile);

        if ($result['success']) {
            return redirect()
                ->to('/admin/hero-sliders')
                ->with('success', $result['message']);
        }

        return redirect()
            ->back()
            ->withInput()
            ->with('errors', [$result['message']]);
    }

    /**
     * Delete slider
     */
    public function delete(int $id): RedirectResponse
    {
        $result = $this->service->deleteSlider($id);

        if ($result['success']) {
            return redirect()
                ->to('/admin/hero-sliders')
                ->with('success', $result['message']);
        }

        return redirect()
            ->back()
            ->with('errors', [$result['message']]);
    }

    /**
     * Update sort order via form submission
     */
    public function updateSortOrder()
    {
        // Get order data from POST (it's a JSON string)
        $orderJson = $this->request->getPost('order');
        
        // Parse JSON string to array
        $orderData = json_decode($orderJson, true);

        // Debug logging
        log_message('debug', 'UpdateSortOrder called with data: ' . print_r($orderData, true));

        $result = $this->service->updateSortOrder($orderData);

        // Debug result
        log_message('debug', 'UpdateSortOrder result: ' . json_encode($result));

        // Always redirect (no AJAX)
        if ($result['success']) {
            return redirect()
                ->to('/admin/hero-sliders')
                ->with('success', $result['message']);
        }

        return redirect()
            ->back()
            ->with('errors', [$result['message'] ?? 'Gagal memperbarui urutan']);
    }

    /**
     * Track view count via AJAX
     */
    public function trackView(int $id)
    {
        if (!$this->request->isAJAX()) {
            throw PageNotFoundException::forPageNotFound();
        }

        $success = $this->service->incrementViewCount($id);

        return $this->response->setJSON([
            'success' => $success,
        ]);
    }
}
