<?php
namespace App\View;

require_once WP_PLUGIN_DIR . '/LogSearch/App/Model/PagiNationModel.class.php';

use App\Model\PagiNationModel;

/**
 * ページネーション    
 * @author Yoshifumi
 *
 */
class PagiNationView
{
    /**
     * 
     * @param PagiNationModel $pagiNationModel
     */
    public function display(PagiNationModel $pagiNationModel)
    {
        if($pagiNationModel->endPage == 1)
        {
            return;
        }

        ob_start();
?>
<table style="margin: auto;">
    <tbody>
        <tr>
          <?php if($pagiNationModel->isDisplayForward()) : ?>
            <td>
                <a class="pagi-nation" href="javascript:log_search.submit();" paged="<?php echo $pagiNationModel->currentPage-1; ?>">&lt;</a>
            </td>
            <td>
                <a class="pagi-nation" href="javascript:log_search.submit();" paged="<?php echo $pagiNationModel->currentPage-1; ?>">前へ</a>
            </td>
          <?php endif; ?>
          <?php for($i = $pagiNationModel->startPage; $i <= $pagiNationModel->endPage; $i++ ) : ?>
            <td>
                    <?php if($i == $pagiNationModel->currentPage) : ?>
                        <?php echo $i; ?>
                    <?php else :?>
                        <a class="pagi-nation" href="javascript:log_search.submit();" paged="<?php echo $i; ?>"><?php echo $i; ?></a>
                    <?php endif; ?>
            </td>
          <?php endfor;?>  
          <?php if($pagiNationModel->isDisplayNext()) : ?>
            <td>
                <a class="pagi-nation" href="javascript:log_search.submit();" paged="<?php echo $pagiNationModel->currentPage+1; ?>">次へ</a>
            </td>
            <td>
                <a class="pagi-nation" href="javascript:log_search.submit();" paged="<?php echo $pagiNationModel->currentPage+1; ?>">&gt;</a>
            </td>
          <?php endif; ?>
        </tr>
    </tbody>
</table>
<?php
        ob_end_flush();
    }
}
