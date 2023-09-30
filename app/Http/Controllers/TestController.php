<?php

namespace Lms\Course\Http\Controllers;

use stdClass;

use App\Http\Controllers\Controller;
use App\Http\Resources\Lms\HomePageResource;
use App\Repositories\Interfaces\CommentRepositoryInterface;
use App\Repositories\Interfaces\CategoryRepositoryInterface;
use Lms\Course\Http\Models\Test;

class ModuleHomeController extends Controller
{
    private CategoryRepositoryInterface $categoryRepository;
    private CommentRepositoryInterface $commentRepository;

    public function __construct(
        CategoryRepositoryInterface $categoryRepository,
        CommentRepositoryInterface $commentRepository,
    ) {
        $this->categoryRepository = $categoryRepository;
        $this->commentRepository = $commentRepository;
    }

    public function index()
    {
        return Test::all();
        $data = new stdClass();
        $data->landingBannerUrl = 'first url';
        $data->categories = $this->categoryRepository->getCategoriesWithCourses();
        $data->outPutSqureUpBannerUrl = 'second url';
        $data->outPutSqureDownBannerUrl = 'third url';
        $data->outPutRectangleBannerUrl = 'fourth url';
        $data->comments = $this->commentRepository->getStarComments();

        return response()->json(new HomePageResource($data));
    }

}
