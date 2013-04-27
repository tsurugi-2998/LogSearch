<?php
namespace App\View;

require_once WP_PLUGIN_DIR . '/LogSearch/App/Model/SummaryModel.class.php';
require_once WP_PLUGIN_DIR . '/LogSearch/App/Constant/LogSearchConstant.class.php';

use App\Constant\LogSearchConstant;
use App\Model\SummaryModel;

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
        ob_start();
?>
<?php if(!isset($summaryModelList)) : ?>
    <br/><strong>検索結果0件です</strong><br/>
<?php else : ?>
    <?php echo '<br/><strong>' . $foundPosts . '件ヒットしました</strong><br/>'; ?>
<?php endif; ?>
<div id="result">
<table id="summary-list">
    <thead>
        <tr>
            <th>登山スタイル</th>
            <th>山域</th>
            <th>実施日</th>
            <th>記録</th>
            <th>画像</th>
            <th>記事</th>
            <th>投稿日</th>
            <th style="background-color: #FFFFFF;"></th>
        </tr>
    </thead>
    <tbody>
<?php
        foreach ($summaryModelList as $summaryModel) :
?>
        <tr>
            <td>
                <?php echo htmlspecialchars($summaryModel->mounteneeringStyleName); ?>
            </td>
            <td>
                <?php echo htmlspecialchars($summaryModel->areaName); ?>
            </td>
            <td>
                <?php echo htmlspecialchars($summaryModel->startDate); ?>～<?php echo htmlspecialchars($summaryModel->endDate); ?>
            </td>
            <td>
                <?php echo htmlspecialchars($summaryModel->logger); ?>
            </td>
            <td>
                <?php if(isset($summaryModel->dummyUrl)) : ?>
                    <img src="<?php echo htmlspecialchars($summaryModel->dummyUrl); ?>" alt="">
                <?php else : ?>
                    <a href="<?php echo $summaryModel->postUrl; ?>">
                        <img class="thumbnail" src="<?php echo htmlspecialchars($summaryModel->thumbnailUrl); ?>" alt="">
                    </a>
                <?php  endif; ?>
            </td>
            <td>
                <a href="<?php echo $summaryModel->postUrl; ?>"><?php echo htmlspecialchars($summaryModel->postTitle); ?></a>
            </td>
            <td>
                <?php echo htmlspecialchars($summaryModel->postDate); ?>
            </td>
            <td><img class="dummy" src="<?php echo htmlspecialchars(site_url() . LogSearchConstant::DUMMY_GIF); ?>" alt="" style="background-color: #FFFFFF;"></td>
        </tr>
<?php 
        endforeach;
?>
    </tbody>
</table>
</div>
<?php
        ob_end_flush();
    }
}