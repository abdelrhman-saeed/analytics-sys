<?php

namespace AnalyticsSystem\DTO;

use AbdelrhmanSaeed\DTO\DTO;


class OrderDTO extends DTO
{
    public function rules(): void {
        $this->input('products')->required()->array();
    }
}
