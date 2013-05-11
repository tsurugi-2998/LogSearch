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
<div class="pagination pagination-centered">
  <ul>
    <?php if($pagiNationModel->isDisplayForward()) : ?>
      <li><a class="pagi-nation" href="javascript:log_search.submit();" paged="<?php echo $pagiNationModel->currentPage-1; ?>">前へ</a></li>
    <?php endif; ?>
    <?php for($i = $pagiNationModel->startPage; $i <= $pagiNationModel->endPage; $i++ ) : ?>
      <?php if($i == $pagiNationModel->currentPage) : ?>
        <li><a class="pagi-nation" href="javascript:log_search.submit();" paged="<?php echo $i; ?>"><?php echo $i; ?></a></li>
      <?php else :?>
        <li><a class="pagi-nation" href="javascript:log_search.submit();" paged="<?php echo $i; ?>"><?php echo $i; ?></a></li>
      <?php endif; ?>
    <?php endfor;?>
    <?php if($pagiNationModel->isDisplayNext()) : ?>
      <li><a class="pagi-nation" href="javascript:log_search.submit();" paged="<?php echo $pagiNationModel->currentPage+1; ?>">次へ</a></li>
    <?php endif; ?>
  </ul>
</div>
<?php
        ob_end_flush();
    }
}
