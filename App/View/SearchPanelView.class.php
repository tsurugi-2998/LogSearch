<?php
namespace App\View;

require_once WP_PLUGIN_DIR . '/LogSearch/App/Model/SearchModel.class.php';

use App\Model\SearchModel;

/**
 * 検索条件パネル
 */
class SearchPanelView
{

    /**
     * 検索条件パネルを表示する
     *
     * @param SearchModel $searchModel
     */
    public function display(SearchModel $searchModel)
    {
        ob_start();
?>
<div id="log_search_panel">
    <form id="log_search" action="" method="post">
        <input type="hidden" name="event" value="LogSearch"/>
        <input type="hidden" id="paged" name="paged" value="1" />
        <table id="log-search-main">
            <tbody>
                <tr>
                    <th>登山スタイル・山域：</th>
                    <td>
                        <select name="mounteneering_style">
                            <option value="none" <?php if($searchModel->mounteneeringStyle === "none"){ echo 'selected';}?>>▼登山スタイル</option>
                            <option value="all" <?php if($searchModel->mounteneeringStyle === "all"){ echo 'selected';}?>>全て</option>
                            <?php
                                foreach ($searchModel->mounteneeringStyleMap as $key => $val) : 
                            ?>
                                <option value="<?php echo $key; ?>" <?php if($searchModel->mounteneeringStyle === $key){ echo 'selected';}?>><?php echo $val; ?></option>
                            <?php
                                endforeach; 
                            ?>
                        </select>
                        <select name="area">
                            <option value="none" <?php if($searchModel->area === "none"){ echo 'selected';}?>>▼山域</option>
                            <option value="all" <?php if($searchModel->area === "all"){ echo 'selected';}?>>全て</option>
                            <?php
                                foreach ($searchModel->areaMap as $key => $val) : 
                            ?>
                                <option value="<?php echo $key; ?>" <?php if($searchModel->area === $key){ echo 'selected';}?>><?php echo $val; ?></option>
                            <?php
                                endforeach; 
                            ?>
                        </select>
                    </td>
                <tr>
                <tr>
                    <th>キーワード：</th>
                    <td><input type="search" id="keyword" name="keyword" size="30" maxlength="30" value="<?php echo $searchModel->keyword; ?>" autocomplete></td>
                    <td>
                        <table>
                            <tbody>
                                <tr>
                                    <td><input type="radio" id="keyword_content" name="keyword_type" value="1" <?php if($searchModel->keywordType === "1"){ echo 'checked';} ?> ></td>
                                    <td><label for="keyword_content">本文</label></td>
                                    <td><input type="radio" id="keyword_member" name="keyword_type" value="2" <?php if($searchModel->keywordType === "2"){ echo 'checked';} ?>></td>
                                    <td><label for="keyword_member">メンバー</label></td>
                                    <td><input type="radio" id="keyword_logger" name="keyword_type" value="3" <?php if($searchModel->keywordType === "3"){ echo 'checked';} ?>></td>
                                    <td><label for="keyword_logger">記録者</label></td>
                                </tr>
                            </tbody>
                        </table>
                    </td>
                </tr>
                <tr>
                    <th>期間：</th>
                    <td><input type="text" name="start_date" id="start_date" value="<?php echo $searchModel->startDate; ?>" size="15" readonly >から<input type="text" name="end_date" id="end_date" value="<?php echo $searchModel->endDate; ?>" size="15" readonly></td>
                    <td>
                        <table>
                            <tbody>
                                <tr>
                                    <td><input type="radio" id="date_run" name="date_type" value="1" <?php if($searchModel->dateType === "1"){ echo 'checked';} ?>></td>
                                    <td><label for="date_run">山行実施日</label></td>
                                    <td><input type="radio" id="date_post" name="date_type" value="2" <?php if($searchModel->dateType === "2"){ echo 'checked';} ?>></td>
                                    <td><label for="date_post">投稿日</label></td>
                                </tr>
                            </tbody>
                        </table>
                    </td>
                <tr>
            </tbody>
        </table>
        <div id="search-log"><input type="submit" value="絞り込み表示"></div>
        <br/><br/>
    </form>
</div>
<?php
        ob_end_flush();
    }
}
?>