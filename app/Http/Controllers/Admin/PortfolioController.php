<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Portfolio\CreateCategoryAction;
use App\Actions\Portfolio\CreatePortfolioImageAction;
use App\Actions\Portfolio\DeleteCategoryAction;
use App\Actions\Portfolio\DeletePortfolioImageAction;
use App\Actions\Portfolio\ReorderPortfolioImageAction;
use App\Actions\Portfolio\SetPortfolioImageCoverAction;
use App\Actions\Portfolio\TogglePortfolioFeaturedAction;
use App\Actions\Portfolio\UpdateCategoryAction;
use App\DTOs\Portfolio\CreatePortfolioImageDTO;
use App\DTOs\Portfolio\SaveCategoryDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CategoryRequest;
use App\Http\Requests\Admin\ReorderPortfolioImageRequest;
use App\Http\Requests\Admin\StorePortfolioImageRequest;
use App\Models\Category;
use App\Models\PortfolioImage;
use App\Services\AdminPortfolioService;
use App\Support\Result;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class PortfolioController extends Controller
{
    public function index(AdminPortfolioService $portfolio): Response
    {
        return Inertia::render('AdminPortfolio', [
            'images' => $portfolio->images(),
            'categories' => $portfolio->categoryOptions(),
        ]);
    }

    public function store(StorePortfolioImageRequest $request, CreatePortfolioImageAction $action): RedirectResponse
    {
        return $this->flash($action->execute(CreatePortfolioImageDTO::fromRequest($request)));
    }

    public function storeCategory(CategoryRequest $request, CreateCategoryAction $action): RedirectResponse
    {
        return $this->flash($action->execute(SaveCategoryDTO::fromRequest($request)));
    }

    public function updateCategory(CategoryRequest $request, Category $category, UpdateCategoryAction $action): RedirectResponse
    {
        return $this->flash($action->execute($category, SaveCategoryDTO::fromRequest($request)));
    }

    public function destroyCategory(Category $category, DeleteCategoryAction $action): RedirectResponse
    {
        return $this->flash($action->execute($category));
    }

    public function reorder(ReorderPortfolioImageRequest $request, PortfolioImage $image, ReorderPortfolioImageAction $action): RedirectResponse
    {
        return $this->flash($action->execute($image, (int) $request->validated('position')));
    }

    public function toggleFeatured(PortfolioImage $image, TogglePortfolioFeaturedAction $action): RedirectResponse
    {
        return $this->flash($action->execute($image));
    }

    public function setCover(PortfolioImage $image, SetPortfolioImageCoverAction $action): RedirectResponse
    {
        return $this->flash($action->execute($image));
    }

    public function destroy(PortfolioImage $image, DeletePortfolioImageAction $action): RedirectResponse
    {
        return $this->flash($action->execute($image));
    }

    private function flash(Result $result): RedirectResponse
    {
        return back()
            ->with('data', $result->data)
            ->with('success', $result->success)
            ->with('message', $result->message);
    }
}
