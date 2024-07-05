<?php
require __DIR__ . '/function.php';
RegisterPlugin("wCaptcha", "ActivePlugin_wCaptcha");

function ActivePlugin_wCaptcha()
{
  // 接口挂载 - 前端代码
  Add_Filter_Plugin('Filter_Plugin_ViewPost_Template', 'mz_wCaptcha_putcode');
  // 接口挂载 - cmd.php 验证
  Add_Filter_Plugin('Filter_Plugin_Cmd_Begin', 'mz_wCaptcha_verify');
  // 接口挂载 - 为模板添加验证
  Add_Filter_Plugin('Filter_Plugin_Zbp_BuildTemplate', 'mz_wCaptcha_GenTpl');
}

// 接口函数 - 前端代码
function mz_wCaptcha_putcode(&$template)
{
  global $zbp;
  if ($zbp->user->ID > 0 && $zbp->user->Level < 5) {
    $template->SetTags('wCaptcha', '<!--wCaptcha-->');
    return;
  }
  $apiKey = $zbp->Config('mz_wCaptcha')->apiKey;
  // $template->SetTags('wCaptcha', '<div id="mz-wCaptcha"></div>');
  $script = "https://unpkg.com/wcaptcha-js@latest/dist/wcaptcha.umd.js";
  $zbp->footer .= "<script src=\"{$script}\"></script>";
  $zbp->footer .= <<<HTML
<script>
  window.addEventListener('DOMContentLoaded', function() {
    const w = new wcaptcha("{$apiKey}");
    w.bind("#mz-wCaptcha");

    const \$cmtForm = $("#mz-wCaptcha").parent();
    const \$submit = \$cmtForm.find("[name=submit]");

    $("#mz-wCaptcha").fadeOut();
    let proveStatus = "";

    // 当表单内容发生变化时，触发验证
    \$cmtForm.on("input", function() {
      if (proveStatus === "running" || proveStatus === "success") {
        return;
      }

      \$submit.prop("disabled", true);
      $("#mz-wCaptcha").fadeIn();
      proveStatus = "running";

      w.prove().then(prove => {
        const \$prove = $("#mz-wCaptcha input");
        \$prove.val(prove);

        zbp.cookie.set("mz_wCaptcha_prove", prove, 1);

        \$submit.prop("disabled", false);
        proveStatus = "success";
      });
    });

    // 评论成功后重置验证
    zbp.plugin.on("comment.post.success", "mz_wCaptcha", function() {
      $("#mz-wCaptcha").fadeOut();
      proveStatus = "";
      w.getProblem();
    });
  })
</script>
HTML;
}

// 接口函数 - cmd.php 验证
function mz_wCaptcha_verify()
{
  global $action;
  // mz_wCaptcha_error($action);
  if ($action !== 'cmt') {
    return;
  }
  $result = "pass";

  $prove = GetVars("mz_wCaptcha_prove", "COOKIE");

  if (empty($prove)) {
    $result = "请先完成验证";
  } else {
    $httpRlt = mz_wCaptcha_http($prove);
    if ($httpRlt->code !== 0 || !$httpRlt->data->is_correct) {
      $result = "验证不通过：" . $httpRlt->message;
    }
  }
  if ($result !== "pass") {
    mz_wCaptcha_error($result);
  }
}

// 接口函数 - 为模板添加验证
function mz_wCaptcha_GenTpl(&$templates)
{
  if (!isset($templates['comment-post'])) {
    $templates['comment-post'] = $templates['commentpost'];
  }
  // 如果模板内有 <!--mz_wCaptcha-->，替换
  if (strpos($templates['comment-post'], "mz-wCaptcha") !== false) {
    $templates['comment-post'] = str_replace('<!--mz_wCaptcha-->', '<div id="mz-wCaptcha"></div>', $templates['comment-post']);
  } elseif (mb_substr_count($templates['comment-post'], "ZC_COMMENT_VERIFY_ENABLE", "UTF-8") == 1) {
    $templates['comment-post'] = str_replace('{if $option[\'ZC_COMMENT_VERIFY_ENABLE\']}', '<div id="mz-wCaptcha"></div>' . "\n" . '{if $option[\'ZC_COMMENT_VERIFY_ENABLE\']}', $templates['comment-post']);
  }
  if (isset($templates['commentpost'])) {
    $templates['commentpost'] = $templates['comment-post'];
  }
}


function InstallPlugin_wCaptcha()
{
  global $zbp;
  $zbp->BuildTemplate();
}

function UninstallPlugin_wCaptcha()
{
  global $zbp;
  $zbp->BuildTemplate();
}
