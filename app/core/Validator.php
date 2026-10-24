<?php
/** Validator - simple rule-based validation returning Arabic-friendly error messages */
class Validator
{
    private array $errors = [];
    private array $data;

    public function __construct(array $data) { $this->data = $data; }

    public function validate(array $rules): self
    {
        foreach ($rules as $field => $ruleString) {
            $rules_arr = explode('|', $ruleString);
            $value = $this->data[$field] ?? null;
            foreach ($rules_arr as $rule) {
                $params = [];
                if (strpos($rule, ':') !== false) {
                    [$rule, $paramStr] = explode(':', $rule, 2);
                    $params = explode(',', $paramStr);
                }
                $this->applyRule($field, $value, $rule, $params);
            }
        }
        return $this;
    }

    private function applyRule(string $field, $value, string $rule, array $params): void
    {
        switch ($rule) {
            case 'required':
                if ($value === null || trim((string)$value) === '') {
                    $this->addError($field, "حقل $field مطلوب");
                }
                break;
            case 'email':
                if (!empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $this->addError($field, "صيغة البريد الإلكتروني غير صحيحة");
                }
                break;
            case 'min':
                if (!empty($value) && mb_strlen((string)$value) < (int)$params[0]) {
                    $this->addError($field, "$field يجب ألا يقل عن {$params[0]} حرفًا");
                }
                break;
            case 'max':
                if (!empty($value) && mb_strlen((string)$value) > (int)$params[0]) {
                    $this->addError($field, "$field يجب ألا يزيد عن {$params[0]} حرفًا");
                }
                break;
            case 'numeric':
                if (!empty($value) && !is_numeric($value)) {
                    $this->addError($field, "$field يجب أن يكون رقمًا");
                }
                break;
            case 'phone':
                if (!empty($value) && !preg_match('/^(77|78|71|73|70)[0-9]{7}$/', preg_replace('/\s+/', '', $value))) {
                    $this->addError($field, "رقم الهاتف غير صحيح");
                }
                break;
            case 'confirmed':
                $confirmField = $field . '_confirmation';
                if (($this->data[$confirmField] ?? null) !== $value) {
                    $this->addError($field, "$field غير متطابق مع التأكيد");
                }
                break;
            case 'in':
                if (!empty($value) && !in_array($value, $params)) {
                    $this->addError($field, "قيمة $field غير صالحة");
                }
                break;
        }
    }

    private function addError(string $field, string $message): void
    {
        $this->errors[$field][] = $message;
    }

    public function fails(): bool { return !empty($this->errors); }
    public function errors(): array { return $this->errors; }
    public function firstError(string $field): ?string { return $this->errors[$field][0] ?? null; }
}
