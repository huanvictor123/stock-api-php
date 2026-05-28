<?php

namespace Helpers;

class Validator
{
    private array $errors = [];
    private array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function required(string $field): self
    {
        if (!isset($this->data[$field]) || (empty($this->data[$field]) && $this->data[$field] !== 0 && $this->data[$field] !== '0')) {
            $this->errors[$field][] = "O campo {$field} e obrigatorio";
        }
        return $this;
    }

    public function string(string $field, int $max = 255): self
    {
        if (isset($this->data[$field]) && strlen((string)$this->data[$field]) > $max) {
            $this->errors[$field][] = "O campo {$field} deve ter no maximo {$max} caracteres";
        }
        return $this;
    }

    public function numeric(string $field, float $min = 0): self
    {
        if (isset($this->data[$field])) {
            if (!is_numeric($this->data[$field])) {
                $this->errors[$field][] = "O campo {$field} deve ser numerico";
            } elseif ((float) $this->data[$field] < $min) {
                $this->errors[$field][] = "O campo {$field} deve ser >= {$min}";
            }
        }
        return $this;
    }

    public function integer(string $field, int $min = 0): self
    {
        if (isset($this->data[$field])) {
            if (!is_int($this->data[$field]) && !ctype_digit((string)$this->data[$field])) {
                $this->errors[$field][] = "O campo {$field} deve ser inteiro";
            } elseif ((int) $this->data[$field] < $min) {
                $this->errors[$field][] = "O campo {$field} deve ser >= {$min}";
            }
        }
        return $this;
    }

    public function fails(): bool
    {
        return !empty($this->errors);
    }

    public function errors(): array
    {
        return $this->errors;
    }
}
