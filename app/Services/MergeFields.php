<?php

namespace App\Services;

class MergeFields
{
    protected array $fields = [];

    protected array $registered = [];

    /**
     * Register a merge field.
     */
    public function register(string $className): void
    {
        if (! class_exists($className)) {
            throw new \InvalidArgumentException(t('class') . $className . t('does_not_exist'));
        }

        if (! method_exists($className, 'name') || ! method_exists($className, 'build')) {
            throw new \InvalidArgumentException(t('class') . $className . t('must_implement'));
        }

        if (! in_array($className, $this->registered)) {
            $this->registered[] = $className;
        }
    }

    /**
     * Load all registered merge fields and their data.
     */
    public function all(): array
    {
        $allFields = [];

        foreach ($this->registered as $className) {
            if (class_exists($className)) {
                $instance = app($className);

                if (! method_exists($instance, 'name') || ! method_exists($instance, 'build')) {
                    continue;
                }

                $allFields[] = [
                    'name'   => $instance->name(),
                    'fields' => $instance->build(),
                    'class'  => $className,
                ];
            }
        }

        return $allFields;
    }

    /**
     * Get fields by a specific name.
     */
    public function getByName(string $name): array
    {
        foreach ($this->all() as $fieldSet) {
            if ($fieldSet['name'] === $name) {
                return $fieldSet['fields'];
            }
        }

        return [];
    }

    /**
     * Get merge fields for a specific template.
     */
    public function getFieldsForTemplate(string $template): array
    {
        $fieldsForTemplate = [];

        foreach ($this->all() as $fieldSet) {
            foreach ($fieldSet['fields'] as $field) {
                if (isset($field['name']) && is_array($fieldSet['fields']) && $template == $fieldSet['name']) {
                    $fieldsForTemplate[] = $field;
                }
            }
        }

        return $fieldsForTemplate;
    }

    public function parseTemplate(string $templateName, string $content, array $context = []): string
    {
        $replacements = [];

        foreach ($this->all() as $fieldSet) {
            if ($fieldSet['name'] === $templateName) {
                $instance = app($fieldSet['class']);
                if (method_exists($instance, 'format')) {
                    $replacements = array_merge($replacements, $instance->format($context));
                }
            }
        }

        return str_replace(array_keys($replacements), array_values($replacements), $content);
    }

    public function parseTemplates($groups, string $content, array $context): string
    {
        $replacements = [];

        // Check if $groups is an array or string
        if (is_array($groups)) {
            foreach ($groups as $template) {
                foreach ($this->all() as $fieldSet) {
                    if ($fieldSet['name'] === $template) {
                        $instance = app($fieldSet['class']);
                        if (method_exists($instance, 'format')) {
                            $replacements = array_merge($replacements, $instance->format($context));
                        }
                    }
                }
            }
        } else {
            // Single group string
            foreach ($this->all() as $fieldSet) {
                if ($fieldSet['name'] === $groups) {
                    $instance = app($fieldSet['class']);
                    if (method_exists($instance, 'format')) {
                        $replacements = array_merge($replacements, $instance->format($context));
                    }
                }
            }
        }

        return str_replace(array_keys($replacements), array_values($replacements), $content);
    }

    /**
     * Get merge fields for a specific email template slug
     */
    public function getFieldsByTemplateSlug(string $slug): array
    {
        $fields = [];

        foreach ($this->registered as $className) {
            $instance = app($className);

            if (! method_exists($instance, 'templates')) {
                continue;
            }

            if (in_array($slug, $instance->templates())) {
                foreach ($instance->build() as $field) {
                    // Skip fields marked as absent for this template
                    if (isset($field['absent']) && in_array($slug, $field['absent'])) {
                        continue;
                    }
                    $fields[] = $field;
                }
            }
        }

        return $fields;
    }

    /**
     * Get merge fields grouped by their groups for a specific template slug
     */
    public function getGroupedFieldsByTemplateSlug(string $slug): array
    {
        $groupedFields = [];

        foreach ($this->registered as $className) {
            $instance = app($className);

            if (! method_exists($instance, 'templates')) {
                continue;
            }

            if (in_array($slug, $instance->templates())) {
                $groupName      = $instance->name();
                $filteredFields = [];

                foreach ($instance->build() as $field) {
                    // Filter out fields marked as absent for this template
                    if (isset($field['absent']) && in_array($slug, $field['absent'])) {
                        continue;
                    }
                    $filteredFields[] = $field;
                }

                $groupedFields[$groupName] = $filteredFields;
            }
        }

        return $groupedFields;
    }
}
