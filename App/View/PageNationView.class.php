<?php
namespace App\View;

require_once WP_PLUGIN_DIR . '/LogSearch/App/Model/PageNationModel.class.php';

use App\Model\PageNationModel;

/**
 * ページネーション    
 * @author Yoshifumi
 *
 */
class PageNationView
{
    /**
     * 
     * @param PageNationModel $pageNationModel
     */
    public function display(PageNationModel $pageNationModel)
    {
        if($pageNationModel->endPage == 1)
        {
            return;
        }

        ob_start();
?>
<table style="margin: auto;">
    <tbody>
        <tr>
          <?php if($pageNationModel->isDisplayForward()) : ?>
            <td>
                <a class="page-nation" href="javascript:log_search.submit();" paged="<?php echo $pageNationModel->currentPage-1; ?>">&lt;</a>
            </td>
            <td>
                <a class="page-nation" href="javascript:log_search.submit();" paged="<?php echo $pageNationModel->currentPage-1; ?>">前へ</a>
            </td>
          <?php endif; ?>
          <?php for($i = $pageNationModel->startPage; $i <= $pageNationModel->endPage; $i++ ) : ?>
            <td>
                    <?php if($i == $pageNationModel->currentPage) : ?>
                        <?php echo $i; ?>
                    <?php else :?>
                        <a class="page-nation" href="javascript:log_search.submit();" paged="<?php echo $i; ?>"><?php echo $i; ?></a>
                    <?php endif; ?>
            </td>
          <?php endfor;?>  
          <?php if($pageNationModel->isDisplayNext()) : ?>
            <td>
                <a class="page-nation" href="javascript:log_search.submit();" paged="<?php echo $pageNationModel->currentPage+1; ?>">次へ</a>
            </td>
            <td>
                <a class="page-nation" href="javascript:log_search.submit();" paged="<?php echo $pageNationModel->currentPage+1; ?>">&gt;</a>
            </td>
          <?php endif; ?>
        </tr>
    </tbody>
</table>
<?php
        ob_end_flush();
    }
}
