<?php
namespace App\View;

require_once WP_PLUGIN_DIR . '/LogSearch/App/Model/SummaryModel.class.php';
require_once WP_PLUGIN_DIR . '/LogSearch/App/Constant/LogSearchConstant.class.php';
require_once ABSPATH . '/FirePHPCore/FirePHP.class.php';

use App\Constant\LogSearchConstant;
use App\Model\SummaryModel;
use FirePHP;
/**
 * 検索結果一覧表示
 *
 * @author Yoshifumi
 *
 */
class SummaryListView
{
    /**
     * 検索結果一覧を表示する
     *
     * @param SummaryModel $summaryModel
     */
    public function display($summaryModelList, $foundPosts)
    {
        $firephp = FirePHP::getInstance(true);
        ob_start();
?>
<div id="errorMessageBox" class="alert alert-error" style="display: none;">
  <ul>
    <li><label for="keyword" class="error"></label></li>
    <li><label for="start_date" class="error"></label></li>
  </ul>
</div>
<div class="alert alert-info">
<?php if(!isset($summaryModelList) || count($summaryModelList) == 0) : ?>
    検索結果0件です
<?php else : ?>
    <?php echo $foundPosts . '件ヒットしました'; ?>
<?php endif; ?>
</div>
<div id="result">
<table id="summary-list">
    <thead>
        <tr>
            <th>山域</th>
            <th>種別</th>
            <th>日程</th>
            <th style="width:60px;">写真</th>
            <th>リーダー</th>
            <th>山行名</th>
            <th>形態</th>
        </tr>
    </thead>
    <tbody>
<?php
        foreach ($summaryModelList as $summaryModel) :
        $firephp->log($summaryModel, 'Summary Model.');
?>
        <tr style="height: 50px;">
            <td>
                <?php echo htmlspecialchars($summaryModel->areaName); ?>
            </td>
            <td>
                <?php echo htmlspecialchars($summaryModel->typeName); ?>
            </td>
            <td>
                <?php echo date('Y年m月', strtotime($summaryModel->startDate)) ?>
            </td>
            <td style="width:60px;">
                <?php if(isset($summaryModel->dummyUrl)) : ?>
                    <img src="<?php echo htmlspecialchars($summaryModel->dummyUrl); ?>" alt="">
                <?php elseif($summaryModel->isOpen == true) : ?>
                    <a href="<?php echo $summaryModel->postUrl; ?>">
                        <img class="thumbnail" src="<?php echo htmlspecialchars($summaryModel->thumbnailUrl); ?>" alt="">
                    </a>
                <?php else : ?>
                    <a href="" onclick="return false" style="cursor:default;">
                        <img class="thumbnail" src="<?php echo htmlspecialchars($summaryModel->thumbnailUrl); ?>" alt="">
                    </a>
                <?php  endif; ?>
            </td>
            <td>
                <label class="member-popover"  data-title="参加者" data-content="<?php echo htmlspecialchars($summaryModel->member); ?>" data-trigger="hover"><?php echo htmlspecialchars($summaryModel->leader); ?></label>
            </td>
            <td class="title">
              <?php if($summaryModel->isOpen == true || is_user_logged_in()) : ?>
                <a class="content-popover" href="<?php echo $summaryModel->postUrl; ?>" data-title="<?php echo htmlspecialchars($summaryModel->postTitle); ?>" data-content="<?php echo htmlspecialchars($summaryModel->content); ?>" data-trigger="hover" data-placement="top"><?php echo htmlspecialchars($summaryModel->postTitle); ?></a>
              <?php else : ?>
                <?php echo htmlspecialchars($summaryModel->postTitle); ?>
              <?php endif; ?>
            </td>
            <td>
                <?php echo htmlspecialchars($summaryModel->styleName); ?>
            </td>
        </tr>
<?php 
        endforeach;
?>
    </tbody>
</table>
<?php
        ob_end_flush();
    }
}