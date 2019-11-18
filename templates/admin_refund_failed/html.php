<?php
/** @var \IngenicoClient\MailTemplate $view */
/** @var $shop_name */
/** @var $shop_logo */
/** @var $shop_url */
/** @var $customer_name */
/** @var $order_reference */
/** @var $ingenico_logo */
?>
<?php echo $view->__('admin_refund_failed.hello', [], 'email'); ?><br/>,
<?php echo $view->__('admin_refund_failed.text1', ['%order%' => $order_reference], 'email'); ?><br/>
<?php echo $view->__('admin_refund_failed.text2', [], 'email'); ?><br/>
<?php echo $view->__('admin_refund_failed.text3', [], 'email'); ?><br/>
<?php echo $view->__('admin_refund_failed.regards', [], 'email'); ?>,<br/>
<?php echo $view->__('admin_refund_failed.team', [], 'email'); ?><br/>
