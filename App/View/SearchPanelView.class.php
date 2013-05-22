<?php
namespace App\View;

require_once WP_PLUGIN_DIR . '/LogSearch/App/Constant/LogSearchConstant.class.php';
require_once WP_PLUGIN_DIR . '/LogSearch/App/Model/SearchModel.class.php';
require_once WP_PLUGIN_DIR . '/LogSearch/App/Model/TaxonomyModel.class.php';

use App\Constant\LogSearchConstant;
use App\Model\SearchModel;
use App\Helper\LogSearchHelper;
use App\Model\TaxonomyModel;

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
<i class="icon-search"></i><strong>山行記録検索</strong>
<br><br>
<div id="log_search_panel">
    <form id="log_search" action="" method="post">
        <input type="hidden" name="event" value="LogSearch"/>
        <input type="hidden" id="paged" name="paged" value="1" />
        <table id="log-search-main">
            <tbody>
                <tr>
                    <th>形態・山域<?php if(is_user_logged_in()){echo '・種別';}?>：</th>
                    <td>
                        <!-- 形態 -->
                        <input type="hidden" id="styleId" name="styleId" value="<?php echo htmlspecialchars($searchModel->styleId); ?>">
                        <input type="hidden" id="styleName" name="styleName" value="<?php echo htmlspecialchars($searchModel->styleName); ?>">
                        <div id="style-btn-group" class="btn-group">
                          <button id="style-label" class="btn btn-primary" data-value="-1" ><?php echo htmlspecialchars($searchModel->styleName); ?></button>
                          <button class="btn btn-primary dropdown-toggle" data-toggle="dropdown">
                            <span class="caret"></span>
                          </button>
                          <ul class="dropdown-menu">
                              <li><a href="javascript:void(0)" style="text-decoration:none;" data-value="0" >全て</a></li>
                              <?php $this->displayTaxonomy($searchModel->styleArray); ?>
                          </ul>
                        </div>
                        <!-- 山域 -->
                        <input type="hidden" id="areaId" name="areaId" value="<?php echo htmlspecialchars($searchModel->areaId); ?>">
                        <input type="hidden" id="areaName" name="areaName" value="<?php echo htmlspecialchars($searchModel->areaName); ?>">
                        <div id="area-btn-group" class="btn-group">
                          <button id="area-label" class="btn btn-primary" data-value="-1" ><?php echo htmlspecialchars($searchModel->areaName); ?></button>
                          <button class="btn btn-primary dropdown-toggle" data-toggle="dropdown">
                            <span class="caret"></span>
                          </button>
                          <ul class="dropdown-menu">
                              <li><a href="javascript:void(0)" style="text-decoration:none;" data-value="0" >全て</a></li>
                              <?php $this->displayTaxonomy($searchModel->areaArray); ?>
                          </ul>
                        </div>
                        <!-- 種別 -->
                        <input type="hidden" id="typeId" name="typeId" value="<?php echo htmlspecialchars($searchModel->typeId); ?>">
                        <input type="hidden" id="typeName" name="typeName" value="<?php echo htmlspecialchars($searchModel->typeName); ?>">
                        <div id="type-btn-group" class="btn-group">
                          <button id="type-label" class="btn btn-primary" data-value="-1" ><?php echo htmlspecialchars($searchModel->typeName); ?></button>
                          <button class="btn btn-primary dropdown-toggle" data-toggle="dropdown">
                            <span class="caret"></span>    
                          </button>
                          <ul class="dropdown-menu">
                              <li><a href="javascript:void(0)" style="text-decoration:none;" data-value="0" >全て</a></li>
                              <?php $this->displayTaxonomy($searchModel->typeArray); ?>
                          </ul>
                        </div>
                    </td>
                <tr>
                <tr>
                    <th>キーワード：</th>
                    <td><input type="search" id="keyword" name="keyword" size="30" maxlength="30" value="<?php echo htmlspecialchars($searchModel->keyword); ?>" autocomplete></td>
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
                    <td><input type="text" name="start_date" id="start_date" value="<?php echo htmlspecialchars($searchModel->startDate); ?>" size="15" readonly >から<input type="text" name="end_date" id="end_date" value="<?php echo htmlspecialchars($searchModel->endDate); ?>" size="15" readonly></td>
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
        <div id="search-log"><button type="submit" class="btn btn-primary" data-loading-text="検索中...">検索</button></div>
        <br/><br/>
    </form>
</div>
<?php
        ob_end_flush();
    }

    private function displayTaxonomy($taxonomyArray)
    {
        foreach ($taxonomyArray as $taxonomyModel)
        {
            if(isset($taxonomyModel->children))
            {
                echo "<li class=\"dropdown-submenu\">\n";
                echo "<a tabindex=\"-1\" href=\"javascript:void(0)\" style=\"text-decoration:none;\" data-value=\"$taxonomyModel->termId\" >$taxonomyModel->name</a>\n";
                echo "<ul class=\"dropdown-menu\">\n";
                $this->displayTaxonomy($taxonomyModel->children);
                echo "</ul>\n";
                echo "</li>\n";
            } else {
                echo "<li><a href=\"javascript:void(0)\" style=\"text-decoration:none;\" data-value=\"$taxonomyModel->termId\" >$taxonomyModel->name</a></li>\n";
            }
        }
    }
}
