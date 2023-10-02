<?php

namespace TechStudio\Blog\app\Services\Banner;

use TechStudio\Blog\app\Models\Banner;

use App\Models\ComBanner;

class BannerService
{
    public function getBannerForHomPage()
    {
        return Banner::select('title','link_url as linkUrl','image_url as imageUrl')->get();
    }
}
