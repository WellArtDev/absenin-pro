<?php

class Validator
{
    private array $errors = [];
    private array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function required(string ...$fields): self
    {
        foreach ($fields as $field) {
            $value = $this->data[$field] ?? null;
            if ($value === null || $value === '') {
                $this->errors[$field] = ucfirst($field) . ' wajib diisi';
            }
        }
        return $this;
    }

    public function email(string $field): self
    {
        $value = $this->data[$field] ?? '';
        if ($value !== '' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field] = 'Format email tidak valid';
        }
        return $this;
    }

    public function minLength(string $field, int $min): self
    {
        $value = $this->data[$field] ?? '';
        if (mb_strlen($value) < $min) {
            $this->errors[$field] = ucfirst($field) . " minimal {$min} karakter";
        }
        return $this;
    }

    public function maxLength(string $field, int $max): self
    {
        $value = $this->data[$field] ?? '';
        if (mb_strlen($value) > $max) {
            $this->errors[$field] = ucfirst($field) . " maksimal {$max} karakter";
        }
        return $this;
    }

    public function numeric(string $field): self
    {
        $value = $this->data[$field] ?? '';
        if ($value !== '' && !is_numeric($value)) {
            $this->errors[$field] = ucfirst($field) . ' harus berupa angka';
        }
        return $this;
    }

    public function date(string $field): self
    {
        $value = $this->data[$field] ?? '';
        if ($value !== '' && !strtotime($value)) {
            $this->errors[$field] = 'Format tanggal tidak valid';
        }
        return $this;
    }

    public function in(string $field, array $allowed): self
    {
        $value = $this->data[$field] ?? '';
        if ($value !== '' && !in_array($value, $allowed, true)) {
            $this->errors[$field] = ucfirst($field) . ' tidak valid';
        }
        return $this;
    }

    public function passes(): bool
    {
        return empty($this->errors);
    }

    public function errors(): array
    {
        return $this->errors;
    }

    public function validate(): array
    {
        if (!$this->passes()) {
            Response::validationError('Validasi gagal', $this->errors);
        }

        return array_intersect_key($this->data, array_flip(array_keys($this->data)));
    }
}
