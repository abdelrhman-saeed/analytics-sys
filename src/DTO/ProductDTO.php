<?php

namespace AnalyticsSystem\DTO;

use AbdelrhmanSaeed\DTO\DTO;


class ProductDTO extends DTO
{
    public function rules(): void {

        $this->input('name')->required();
        $this->input('price')->required()->numeric();

    }
}