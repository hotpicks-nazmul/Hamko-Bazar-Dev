<?php

namespace App\Observers;

use App\Models\ProductQA;

class ProductQAObserver
{
    public function created(ProductQA $question)
    {
        notify_product_question($question);
    }
}
