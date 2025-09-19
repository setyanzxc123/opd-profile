<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\NewsModel;
use App\Models\UserModel;

class News extends BaseController
{
    private function ensureUploadsDir(): string
    {
        $target = FCPATH . 'uploads/news';
        if (! is_dir($target)) {
            @mkdir($target, 0775, true);
        }
        return $target;
    }

    private function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        helper('text');
        $base = url_title($title, '-', true);
        if ($base === '') {
            $base = 'news';
        }
        $slug = $base;
        $model = new NewsModel();
        $i = 2;
        while (true) {
            $existing = $model->where('slug', $slug);
            if ($ignoreId) {
                $existing = $existing->where('id !=', $ignoreId);
            }
            if (! $existing->first()) {
                break;
            }
            $slug = $base . '-' . $i;
            $i++;
        }
        return $slug;
    }

    public function index()
    {
        $model = new NewsModel();
        $items = $model->orderBy('id', 'DESC')->findAll(50);

        return view('admin/news/index', [
            'title' => 'News',
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('admin/news/form', [
            'title' => 'Create News',
            'item'  => [
                'id' => 0,
                'title' => '',
                'slug' => '',
                'content' => '',
                'thumbnail' => '',
                'published_at' => '',
            ],
            'validation' => \Config\Services::validation(),
            'mode' => 'create',
        ]);
    }

    public function store()
    {
        $rules = [
            'title'        => 'required|min_length[3]|max_length[200]',
            'content'      => 'required',
            'published_at' => 'permit_empty',
            'thumbnail'    => 'permit_empty|uploaded[thumbnail]|max_size[thumbnail,4096]|is_image[thumbnail]'
        ];

        if (! $this->request->getFile('thumbnail')->isValid()) {
            $rules['thumbnail'] = 'permit_empty';
        }

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', 'Please correct the errors.');
        }

        helper('activity');

        $model   = new NewsModel();
        $title   = $this->request->getPost('title');
        $slug    = $this->uniqueSlug($title);
        $content = $this->request->getPost('content');
        $publishedAt = $this->request->getPost('published_at');
        if ($publishedAt) {
            $publishedAt = str_replace('T', ' ', $publishedAt) . ':00';
        }

        $thumbPath = null;
        $file = $this->request->getFile('thumbnail');
        if ($file && $file->isValid()) {
            $this->ensureUploadsDir();
            $newName = $file->getRandomName();
            $file->move(FCPATH . 'uploads/news', $newName);
            $thumbPath = 'uploads/news/' . $newName;
        }

        $model->insert([
            'title'        => $title,
            'slug'         => $slug,
            'content'      => $content,
            'thumbnail'    => $thumbPath,
            'published_at' => $publishedAt ?: null,
            'author_id'    => (int) session('user_id'),
        ]);

        log_activity('news.create', 'Menambah berita: ' . $title);

        return redirect()->to(site_url('admin/news'))->with('message', 'News created.');
    }

    public function edit(int $id)
    {
        $model = new NewsModel();
        $item  = $model->find($id);
        if (! $item) {
            return redirect()->to(site_url('admin/news'))->with('error', 'News not found.');
        }

        return view('admin/news/form', [
            'title' => 'Edit News',
            'item'  => $item,
            'validation' => \Config\Services::validation(),
            'mode' => 'edit',
        ]);
    }

    public function update(int $id)
    {
        $model = new NewsModel();
        $item  = $model->find($id);
        if (! $item) {
            return redirect()->to(site_url('admin/news'))->with('error', 'News not found.');
        }

        $rules = [
            'title'        => 'required|min_length[3]|max_length[200]',
            'content'      => 'required',
            'published_at' => 'permit_empty',
            'thumbnail'    => 'permit_empty|uploaded[thumbnail]|max_size[thumbnail,4096]|is_image[thumbnail]'
        ];

        if (! $this->request->getFile('thumbnail')->isValid()) {
            $rules['thumbnail'] = 'permit_empty';
        }

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', 'Please correct the errors.');
        }

        helper('activity');

        $title   = $this->request->getPost('title');
        $slug    = $this->uniqueSlug($title, $id);
        $content = $this->request->getPost('content');
        $publishedAt = $this->request->getPost('published_at');
        if ($publishedAt) {
            $publishedAt = str_replace('T', ' ', $publishedAt) . ':00';
        }

        $data = [
            'title'        => $title,
            'slug'         => $slug,
            'content'      => $content,
            'published_at' => $publishedAt ?: null,
        ];

        $file = $this->request->getFile('thumbnail');
        if ($file && $file->isValid()) {
            $this->ensureUploadsDir();
            $newName = $file->getRandomName();
            $file->move(FCPATH . 'uploads/news', $newName);
            $data['thumbnail'] = 'uploads/news/' . $newName;
        }

        $model->update($id, $data);

        log_activity('news.update', 'Mengubah berita: ' . $title);

        return redirect()->to(site_url('admin/news'))->with('message', 'News updated.');
    }

    public function delete(int $id)
    {
        helper('activity');
        $model = new NewsModel();
        $item  = $model->find($id);
        if ($item) {
            $model->delete($id);
            log_activity('news.delete', 'Menghapus berita: ' . ($item['title'] ?? ''));
        }
        return redirect()->to(site_url('admin/news'))->with('message', 'News deleted.');
    }
}
