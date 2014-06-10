<?php

namespace Zidisha\Html;

use Illuminate\Config\Repository as Config;
use Illuminate\Html\HtmlBuilder;
use Illuminate\Html\FormBuilder;
use Illuminate\Session\SessionManager as Session;

class BootstrapFormBuilder
{
    /**
     * Illuminate HtmlBuilder instance.
     *
     * @var \Illuminate\Html\FormBuilder
     */
    protected $html;

    /**
     * Illuminate FormBuilder instance.
     *
     * @var \Illuminate\Html\FormBuilder
     */
    protected $form;

    /**
     * Illuminate Repository instance.
     *
     * @var \Illuminate\Config\Repository
     */
    protected $config;

    /**
     * Illuminate SessionManager instance.
     *
     * @var \Illuminate\Session\Store
     */
    protected $session;
    
    protected $translationDomain = '';

    public function __construct(HtmlBuilder $html, FormBuilder $form, Config $config, Session $session)
    {
        $this->html = $html;
        $this->form = $form;
        $this->config = $config;
        $this->session = $session;
    }

    /**
     * Open a form while passing a model and the routes for storing or updating
     * the model. This will set the correct route along with the correct
     * method.
     *
     * @param  array  $options
     * @return string
     */
    public function open(array $options = [])
    {
        $this->translationDomain = array_get($options, 'translationDomain', '');
        unset($options['name']);
        
        return $this->form->open($options);
    }

    /**
     * Create a new model based form builder.
     *
     * @param  mixed  $model
     * @param  array  $options
     * @return string
     */
    public function model($model, array $options = array())
    {
        return $this->form->model($model, $options);
    }

    /**
     * Close the current form.
     *
     * @return string
     */
    public function close() {
        return $this->form->close();
    }
    
    /**
     * Create a Bootstrap text field input.
     *
     * @param  string  $name
     * @param  string  $value
     * @param  array   $options
     * @return string
     */
    public function text($name, $value = null, $options = [])
    {
        return $this->input('text', $name, $value, $options);
    }

    /**
     * Create a Bootstrap email field input.
     *
     * @param  string  $name
     * @param  string  $value
     * @param  array   $options
     * @return string
     */
    public function email($name = 'email', $value = null, $options = [])
    {
        return $this->input('email', $name, $value, $options);
    }

    /**
     * Create a Bootstrap textarea field input.
     *
     * @param  string  $name
     * @param  string  $value
     * @param  array   $options
     * @return string
     */
    public function textarea($name, $value = null, $options = [])
    {
        return $this->input('textarea', $name, $value, $options);
    }

    /**
     * Create a Bootstrap password field input.
     *
     * @param  string  $name
     * @param  array   $options
     * @return string
     */
    public function password($name, $options = [])
    {
        return $this->input('password', $name, null, $options);
    }

    /**
     * Create a Bootstrap label.
     *
     * @param  string  $name
     * @param  string  $value
     * @param  array   $options
     * @return string
     */
    public function label($name, $value = null, $options = [])
    {
        $options = $this->getLabelOptions($options);

        return $this->form->label($name, \Lang::get($value), $options);
    }

    /**
     * Create a Bootstrap submit button.
     *
     * @param  string  $value
     * @param  array   $options
     * @return string
     */
    public function submit($value = null, $options = [])
    {
        $value = $this->translationDomain ? $this->translationDomain . '.' . $value : $value;
        $options = array_merge(['class' => 'btn btn-primary'], $options);

        return $this->form->submit(\Lang::get($value), $options);
    }

    /**
     * Create the input group for an element with the correct classes for errors.
     *
     * @param  string  $type
     * @param  string  $name
     * @param  string  $value
     * @param  array   $options
     * @return string
     */
    protected function input($type, $name, $value = null, $options = [])
    {
        $label = array_get($options, 'label', $this->translationDomain ? $this->translationDomain . '.' . $name : $name);
        unset($options['label']);

        $options = $this->getFieldOptions($options);
        $wrapperOptions = ['class' => $this->getRightColumnClass()];

        $inputElement = $type == 'password' ? $this->form->password($name, $options) : $this->form->{$type}($name, $value, $options);
        
        $groupElement = '<div '.$this->html->attributes($wrapperOptions).'>'.$inputElement.$this->getFieldError($name).'</div>';

        return $this->getFormGroup($name, $label, $groupElement);
    }

    /**
     * Get a form group comprised of a label, form element and errors.
     *
     * @param  string  $name
     * @param  string  $label
     * @param  string  $element
     * @return string
     */
    protected function getFormGroup($name, $label, $element)
    {
        $options = $this->getFormGroupOptions($name);
        $label = $label ? $this->label($name, $label) : '';

        return '<div '.$this->html->attributes($options).'>'. $label .$element.'</div>';
    }

    /**
     * Merge the options provided for a form group with the default options
     * required for Bootstrap styling.
     *
     * @param  string $name
     * @param  array  $options
     * @return array
     */
    protected function getFormGroupOptions($name, $options = [])
    {
        $class = trim('form-group ' . $this->getFieldErrorClass($name));

        return array_merge(['class' => $class], $options);
    }

    /**
     * Merge the options provided for a field with the default options
     * required for Bootstrap styling.
     *
     * @param  array  $options
     * @return array
     */
    protected function getFieldOptions($options = [])
    {
        return array_merge(['class' => 'form-control'], $options);
    }

    /**
     * Merge the options provided for a label with the default options
     * required for Bootstrap styling.
     *
     * @param  array  $options
     * @return array
     */
    protected function getLabelOptions($options = [])
    {
        $class = trim('control-label ' . $this->getLeftColumnClass());

        return array_merge(['class' => $class], $options);
    }

    /**
     * Get the column class for the left class of a horizontal form.
     *
     * @return string
     */
    protected function getLeftColumnClass()
    {
        return $this->config->get('bootstrap-form::left_column') ?: '';
    }

    /**
     * Get the column class for the right class of a horizontal form.
     *
     * @return string
     */
    protected function getRightColumnClass()
    {
        return $this->config->get('bootstrap-form::right_column') ?: '';
    }

    /**
     * Get the MessageBag of errors that is populated by the
     * validator.
     *
     * @return \Illuminate\Support\MessageBag
     */
    protected function getErrors()
    {
        return $this->session->get('errors');
    }

    /**
     * Get the first error for a given field, using the provided
     * format, defaulting to the normal Bootstrap 3 format.
     *
     * @param  string  $field
     * @param  string  $format
     * @return string
     */
    protected function getFieldError($field, $format = '<span class="help-block">:message</span>')
    {
        if ( ! $this->getErrors()) return;

        return $this->getErrors()->first($field, $format);
    }

    /**
     * Return the error class if the given field has associated
     * errors, defaulting to the normal Bootstrap 3 error class.
     *
     * @param  string  $field
     * @param  string  $class
     * @return string
     */
    protected function getFieldErrorClass($field, $class = 'has-error')
    {
        return $this->getFieldError($field) ? $class : null;
    }
}
