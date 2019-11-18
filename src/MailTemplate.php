<?php

namespace IngenicoClient;

use Symfony\Component\Translation\Translator;
use Symfony\Component\Translation\Loader\PoFileLoader;

class MailTemplate
{
    const TYPE_HTML = 'html';
    const TYPE_PLAIN_TEXT = 'text';

    const LAYOUT_DEFAULT = 'default';
    const LAYOUT_INGENICO = 'ingenico';

    const MAIL_TEMPLATE_REMINDER = 'reminder';
    const MAIL_TEMPLATE_REFUND_FAILED = 'refund_failed';
    const MAIL_TEMPLATE_ADMIN_REFUND_FAILED = 'admin_refund_failed';
    const MAIL_TEMPLATE_PAID_ORDER = 'order_paid';
    const MAIL_TEMPLATE_ADMIN_PAID_ORDER = 'admin_order_paid';
    const MAIL_TEMPLATE_AUTHORIZATION = 'authorization';
    const MAIL_TEMPLATE_ADMIN_AUTHORIZATION = 'admin_authorization';
    const MAIL_TEMPLATE_ONBOARDING_REQUEST = 'onboarding_request';
    const MAIL_TEMPLATE_SUPPORT = 'support';

    /**
     * @var string
     */
    private $locale = 'en_US';

    /**
     * @var string
     */
    private $layout = 'default';

    /**
     * @var string
     */
    private $template;

    /**
     * @var array
     */
    private $fields = [];

    /**
     * @var Translator
     */
    private $translator;

    /**
     * MailTemplate constructor.
     *
     * @param $locale
     * @param $layout
     * @param $template
     * @param array $fields
     */
    public function __construct($locale, $layout, $template, $fields = array())
    {
        $this->locale = $locale;
        $this->layout = $layout;
        $this->template = $template;
        $this->fields = $fields;

        // Initialize translations
        $this->translator = new Translator($this->locale);
        $this->translator->addLoader('po', new PoFileLoader());
        $this->translator->setFallbackLocales(['en_US']);
        $this->translator->setLocale($this->locale);

        // Load translations
        $directory = __DIR__ . '/../translations';
        $files = scandir($directory);
        foreach ($files as $file) {
            $file = $directory . DIRECTORY_SEPARATOR . $file;
            $info = pathinfo($file);
            if ($info['extension'] !== 'po') {
                continue;
            }

            $filename = $info['filename'];
            list($domain, $locale) = explode('.', $filename);
            $this->translator->addResource('po', $directory . DIRECTORY_SEPARATOR . $info['basename'], $locale, $domain);
        }
    }

    /**
     * Get Message.
     *
     * @param $type
     *
     * @return false|string
     *
     * @throws Exception
     */
    private function getMessage($type)
    {
        if (!in_array($type, [self::TYPE_HTML, self::TYPE_PLAIN_TEXT])) {
            throw new Exception('Wrong type argument');
        }

        return $this->renderTemplate($this->layout, $this->template, $type, $this->fields);
    }

    /**
     * Get HTML.
     *
     * @return false|string
     *
     * @throws Exception
     */
    public function getHtml()
    {
        return $this->getMessage(self::TYPE_HTML);
    }

    /**
     * Get Plain Text.
     *
     * @return false|string
     *
     * @throws Exception
     */
    public function getPlainText()
    {
        return $this->getMessage(self::TYPE_PLAIN_TEXT);
    }

    /**
     * Render template.
     *
     * @param $layout
     * @param $template
     * @param $type
     * @param array $fields
     *
     * @return false|string
     *
     * @throws Exception
     */
    public function renderTemplate($layout, $template, $type, array $fields)
    {
        $layout = preg_replace('/[^a-zA-Z0-9_-]+/', '', $layout);
        $template = preg_replace('/[^a-zA-Z0-9_-]+/', '', $template);
        $type = preg_replace('/[^a-zA-Z0-9_-]+/', '', $type);

        $fields = array_merge($this->fields, $fields);
        $fields['t'] = function ($id, $parameters = [], $domain = null, $locale = null) {
            echo $this->translator->trans($id, $parameters, $domain, $locale);
        };
        $fields['view'] = &$this;
        $fields['locale'] = $this->locale;
        extract($fields);

        // Render View
        ob_start();
        $templatesDirectory = __DIR__.'/../templates';
        $templateFile = $templatesDirectory.'/'.$template.'/'.$type.'.php';
        if (!file_exists($templateFile)) {
            throw new Exception("Template {$template} don't exits");
        }

        require $templateFile;
        $contents = ob_get_contents();
        ob_end_clean();

        // Override content
        $fields['contents'] = $contents;

        // Render layout
        ob_start();

        $layoutFile = $templatesDirectory.'/layouts/'.$layout.'/'.$type.'.php';
        if (!file_exists($layoutFile)) {
            throw new Exception("Layout {$layout} don't exits");
        }

        require $layoutFile;
        $contents = ob_get_contents();
        ob_end_clean();

        return $contents;
    }

    /**
     * Translate.
     *
     * @param $id
     * @param array $parameters
     * @param string|null $domain
     * @param string|null $locale
     *
     * @return string
     */
    public function __($id, $parameters = [], $domain = null, $locale = null)
    {
        return $this->translator->trans($id, $parameters, $domain, $locale);
    }

    /**
     * Embed Image.
     *
     * @param $file
     *
     * @return string
     */
    public function embedImage($file)
    {
        $size = getimagesize($file);
        if ($size) {
            $contents = file_get_contents($file);

            return sprintf('data:%s;base64,%s', $size['mime'], base64_encode($contents));
        }

        return false;
    }
}
