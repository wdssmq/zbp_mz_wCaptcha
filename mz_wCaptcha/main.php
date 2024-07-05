<?php
require '../../../zb_system/function/c_system_base.php';
require '../../../zb_system/function/c_system_admin.php';
$zbp->Load();
$action = 'root';
if (!$zbp->CheckRights($action)) {
  $zbp->ShowError(6);
  die();
}
if (!$zbp->CheckPlugin('mz_wCaptcha')) {
  $zbp->ShowError(48);
  die();
}

$act = GetVars('act', 'GET');
$suc = GetVars('suc', 'GET');
if ($act == 'save') {
  CheckIsRefererValid();
  foreach ($_POST as $key => $val) {
    if (substr($key, 0, 5) == 'read_') {
      continue;
    }
    $zbp->Config('mz_wCaptcha')->$key = trim($val);
  }
  $zbp->SaveConfig('mz_wCaptcha');
  $zbp->BuildTemplate();
  $zbp->SetHint('good');
  Redirect('./main.php' . ($suc == null ? '' : "?act={$suc}"));
}

$blogtitle = 'wCaptcha';
require $blogpath . 'zb_system/admin/admin_header.php';
require $blogpath . 'zb_system/admin/admin_top.php';
?>
<div id="divMain">
  <div class="divHeader"><?php echo $blogtitle; ?></div>
  <div class="SubMenu">
  </div>
  <div id="divMain2">
    <form action="<?php echo BuildSafeURL("main.php?act=save"); ?>" method="post">
      <table width="100%" class="tableBorder">
        <tr>
          <th width="10%">项目</th>
          <th>内容</th>
          <th width="45%">说明</th>
        </tr>
        <tr>
          <td>API Key</td>
          <td><?php zbpform::text("apiKey", $zbp->Config("mz_wCaptcha")->apiKey, "90%"); ?></td>
          <td>
            <p>创建新 API Key：<a href="https://wcaptcha.pingflash.com/#/start" target="_blank" title="wCaptcha">https://wcaptcha.pingflash.com/#/start</a></p>
          </td>
        </tr>
        <tr>
          <td>API Secret</td>
          <td><?php zbpform::text("apiSecret", $zbp->Config("mz_wCaptcha")->apiSecret, "90%"); ?></td>
          <td>
            <p>配置计算难度：<a href="https://wcaptcha.pingflash.com/#/config" target="_blank" title="wCaptcha">https://wcaptcha.pingflash.com/#/config</a></p>
          </td>
        </tr>
        <tr>
          <td></td>
          <td colspan="2"><input type="submit" value="提交" /></td>
        </tr>
      </table>
    </form>
    <hr>


  </div>
</div>

<?php
require $blogpath . 'zb_system/admin/admin_footer.php';
RunTime();
?>
