<?php

namespace App\Custom;

use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Http\JsonResponse;

class ResponseHelper implements Jsonable{
    protected array $data = [
        "error" => 0,
    ];
    public function error(string $message,$code = 1): static{
        $this->data['error'] = $code;
        $this->message($message);
        return $this;
    }
    public function message(string $message): static{
        $this->data['message'] = $message;
        return $this;
    }
    public function json($data = []): JsonResponse{
        return response()->json($data);
    }
    public function toJson($options = 0): array|string{
        return $this->data;
    }
}
