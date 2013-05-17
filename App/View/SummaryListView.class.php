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
<?php if(!isset($summaryModelList) || count($summaryModelList) == 0) : ?>
    <br/><strong>検索結果0件です</strong><br/>
<?php else : ?>
    <?php echo '<br/><strong>' . $foundPosts . '件ヒットしました</strong><br/>'; ?>
<?php endif; ?>
<div id="result">
<table id="summary-list">
    <thead>
        <tr>
            <th>形態</th>
            <th>山域</th>
            <th>種別</th>
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
        $firephp->log($summaryModel, 'Summary Model.');
?>
        <tr <?php if($summaryModel->isOpen != true){ echo "class=\"close-row\"";}?>>
            <td>
                <?php echo htmlspecialchars($summaryModel->styleName); ?>
            </td>
            <td>
                <?php echo htmlspecialchars($summaryModel->areaName); ?>
            </td>
            <td>
                <?php echo htmlspecialchars($summaryModel->typeName); ?>
            </td>
            <td>
                <?php echo date('Y年m月', strtotime($summaryModel->startDate)) ?>
            </td>
            <td>
                <label class="member-popover"  data-title="参加者" data-content="<?php echo htmlspecialchars($summaryModel->member); ?>" data-trigger="hover"><?php echo htmlspecialchars($summaryModel->logger); ?></label>
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
            <td class="title">
                <a class="content-popover" href="<?php echo $summaryModel->postUrl; ?>" data-title="<?php echo htmlspecialchars($summaryModel->postTitle); ?>" data-content="<?php echo htmlspecialchars($summaryModel->content); ?>" data-trigger="hover" data-placement="top"><?php echo htmlspecialchars($summaryModel->postTitle); ?></a>
            </td>
            <td>
                <?php echo htmlspecialchars($summaryModel->postDate); ?>
            </td>
            <td style="background-color: #FFFFFF;"><img class="dummy" src="<?php echo htmlspecialchars(site_url() . LogSearchConstant::DUMMY_GIF); ?>" alt="" style="background-color: #FFFFFF;"></td>
        </tr>
<?php 
        endforeach;
?>
        <tr style="background-color: #FFFFFF;">
            <td style="background-color: #FFFFFF;"></td>
            <td style="background-color: #FFFFFF;"></td>
            <td style="background-color: #FFFFFF;"></td>
            <td style="background-color: #FFFFFF;"></td>
            <td style="background-color: #FFFFFF;"><img class="dummy" src="<?php echo htmlspecialchars(site_url() . LogSearchConstant::DUMMY_GIF); ?>" alt="" style="background-color: #FFFFFF;"></td>
            <td style="background-color: #FFFFFF;"></td>
            <td style="background-color: #FFFFFF;"></td>
            <td style="background-color: #FFFFFF;"><img class="dummy" src="<?php echo htmlspecialchars(site_url() . LogSearchConstant::DUMMY_GIF); ?>" alt="" style="background-color: #FFFFFF;"></td>
        </tr>
    </tbody>
</table>
</div>
<?php
        ob_end_flush();
    }
}