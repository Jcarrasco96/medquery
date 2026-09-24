<?php

declare(strict_types=1);

namespace app\utils\medicaid;

final readonly class PageAnalysis
{
    public function __construct(
        public PageState $state,
        public ?GenericForm $form = null,
        public array $images = []
    )
    {

    }
}