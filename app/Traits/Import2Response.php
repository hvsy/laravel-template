<?php

namespace App\Traits;

use JetBrains\PhpStorm\ArrayShape;

trait Import2Response{
    private int $rowNumber = 0;

    public function getRowNumber(): int{
        return $this->rowNumber;
    }
    public function increment(): static{
        ++$this->rowNumber;
        return $this;
    }

    #[ArrayShape(['count' => "int", 'failures' => "mixed"])]
    public function toResponse(): array{
        $head = $this->headingRow();
        return [
            'count'=>$this->getRowNumber(),
            'failures'=>$this->failures()->map(function($item) use ($head){
                return [
                    'row'=>$item->row()-$head,
                    'attribute'=>$item->attribute(),
                    'value'=>$item->values()[$item->attribute()] ?? '',
                    'errors'=>$item->errors(),
                ];
            })
        ];
    }
}
