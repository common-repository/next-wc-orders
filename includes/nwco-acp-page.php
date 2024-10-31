<?php
$nwco_CurrentVersion = get_option('nwcoCurrentVersion');
$nwco_CurrentType = get_option('nwcoCurrentType');

$opt_ShowOrderProducts = get_option('optShowOrderProducts');
$opt_StatusCompleted = get_option('optStatusCompleted');   if ($opt_StatusCompleted == "") $opt_StatusCompleted = "#00ff00";
$opt_StatusOnhold = get_option('optStatusOnhold');         if ($opt_StatusOnhold == "") $opt_StatusOnhold = "#ff7700";
$opt_StatusProcessing = get_option('optStatusProcessing'); if ($opt_StatusProcessing == "") $opt_StatusProcessing = "#ff7700";
$opt_StatusPending = get_option('optStatusPending');       if ($opt_StatusPending == "") $opt_StatusPending = "#ff7700";
$opt_StatusRefunded = get_option('optStatusRefunded');     if ($opt_StatusRefunded == "") $opt_StatusRefunded = "#ff0000";
$opt_StatusCancelled = get_option('optStatusCancelled');   if ($opt_StatusCancelled == "") $opt_StatusCancelled = "#ff0000";
$opt_StatusFailed = get_option('optStatusFailed');         if ($opt_StatusFailed == "") $opt_StatusFailed = "#ff0000";
$opt_Pie3D = get_option('optPie3D');

$A_BasicColor[1] = "#ff0000";
$A_BasicColor[2] = "#ffff00";
$A_BasicColor[3] = "#00ff00";
$A_BasicColor[4] = "#00ffff";
$A_BasicColor[5] = "#0000ff";
$A_BasicColor[6] = "#ff00ff";
$A_BasicColor[7] = "#ff8000";
$A_BasicColor[8] = "#ff0080";
$A_BasicColor[9] = "#00ff80";
$A_BasicColor[10] = "#0080ff";
  
$tmpYears = nwco_GetOrderYears();
$listYears = explode("|",$tmpYears);
$n = count($listYears)-1;
for ($c=1;$c<$n;$c++)
    { $listY = explode("=",$listYears[$c]);
      $tmpOptPie = 'optPie'.$listY[0];
      ${'opt_Pie'.$listY[0]} = get_option($tmpOptPie);
      if (${'opt_Pie'.$listY[0]} == "") ${'opt_Pie'.$listY[0]} = $A_BasicColor[$c];
    }
    
$opt_Shipping = get_option('optShipping');
$opt_Year = get_option('optYear');
$selectedYear = $opt_Year;
$opt_Prod = get_option('optProd');
$opt_Cat = get_option('optCat');
$opt_Status = get_option('optStatus');
$opt_Total = get_option('optTotal');

$tmpVersionGD = "";
if (function_exists('gd_info'))
   { $A_InfoGD = gd_info();
     $tmpVersionGD = (empty($A_InfoGD['GD Version'])?"Not found!":$A_InfoGD['GD Version']);
   }
echo '<div align="right">' . esc_attr($nwco_CurrentType) . ' Version v.' . esc_attr($nwco_CurrentVersion) . ' - (GD Version: ' . esc_attr($tmpVersionGD)  . ')</div>';
  
if(!empty($_POST['do']))
  { $strDateTime = date('Ymd-His');
	  switch($_POST['do'])
	        { case 'Export CSV':
	               ob_end_clean ();
                 $fd = @fopen( 'php://output', 'w' );
                 header("Content-disposition: attachment; filename = next-wc-orders_" . $strDateTime . ".csv");
                 nwco_Orders_CSV($fd,$opt_Shipping,$selectedYear,$opt_Prod,$opt_Cat,$opt_Status,$opt_Total);
                 fclose($fd);
                 exit;
                 break;
         
            case 'Export HTML':
                 ob_end_clean ();
                 $fd = @fopen( 'php://output', 'w' );
                 header("Content-disposition: attachment; filename = next-wc-orders_" . $strDateTime . ".htm");
                 nwco_Orders_HTML($opt_Shipping,$selectedYear,$opt_Prod,$opt_Cat,$opt_Status,$opt_Total);
                 fclose($fd);
                 exit;
                 break;
	               
	           default:
	                 break;
	        }
  }

$tmpTab = sanitize_text_field($_GET['tab']);
$tab = (isset($tmpTab) and $tmpTab != "")?$tmpTab:'nwco_orders';
$tmpSection = sanitize_text_field($_GET['section']);
if($tab==='nwco_stats')
  { $section = (isset($tmpSection) and $tmpSection != "")?$tmpSection:"general";
  }
?>

<div class="wrap">
<nav class="nav-tab-wrapper">
     <a href="?page=nwco-acp&tab=nwco_settings" class="nav-tab <?php if($tab==='nwco_settings'):?>nav-tab-active<?php endif; ?>"><?php esc_html_e('Settings','next-wc-orders'); ?></a>
     <a href="?page=nwco-acp&tab=nwco_orders" class="nav-tab <?php if($tab==='nwco_orders'):?>nav-tab-active<?php endif; ?>"><?php esc_html_e('Orders','next-wc-orders'); ?></a>
     <a href="?page=nwco-acp&tab=nwco_stats" class="nav-tab <?php if($tab==='nwco_stats'):?>nav-tab-active<?php endif; ?>"><?php esc_html_e('Stats','next-wc-orders'); ?></a>
</nav>

    <div class="tab-content">
    <?php switch($tab)
          { case 'nwco_settings': ?> 
  
    <form method="post" action="options.php">
    <?php settings_fields('nwco-settings-group'); ?>
    <?php do_settings_sections('nwco-settings-group'); ?>

    <h2 class="title"><?php esc_html_e('Orders','next-wc-orders'); ?></h2>
       
    <table class="form-table">
        <tr valign="top">
        <th scope="row"><?php esc_html_e('Detailed products','next-wc-orders'); ?></th>
        <td><input type="checkbox" name="optShowOrderProducts" value=1 <?php echo($opt_ShowOrderProducts==1?"checked ":"");?>class="wppd-ui-toggle" /> <?php esc_html_e('Shows detailed products list with quantity and price for each order','next-wc-orders'); ?>
        </td></tr>
    </table>    
    
    <table class="form-table">
        <tr valign="top">
        <th scope="row"><?php esc_html_e('Status','next-wc-orders'); ?></th>
        <td>
        <input type="color" name="optStatusCompleted" value="<?php echo esc_attr($opt_StatusCompleted); ?>" class="xxx" /> <?php esc_html_e('Completed','next-wc-orders'); ?><br>
        <input type="color" name="optStatusOnhold" value="<?php echo esc_attr($opt_StatusOnhold); ?>" class="xxx" /> <?php esc_html_e('On hold','next-wc-orders'); ?><br>
        <input type="color" name="optStatusProcessing" value="<?php echo esc_attr($opt_StatusProcessing); ?>" class="xxx" /> <?php esc_html_e('Processing','next-wc-orders'); ?><br>
        <input type="color" name="optStatusPending" value="<?php echo esc_attr($opt_StatusPending); ?>" class="xxx" /> <?php esc_html_e('Pending','next-wc-orders'); ?><br>
        <input type="color" name="optStatusRefunded" value="<?php echo esc_attr($opt_StatusRefunded); ?>" class="xxx" /> <?php esc_html_e('Refunded','next-wc-orders'); ?><br>
        <input type="color" name="optStatusCancelled" value="<?php echo esc_attr($opt_StatusCancelled); ?>" class="xxx" /> <?php esc_html_e('Cancelled','next-wc-orders'); ?><br>
        <input type="color" name="optStatusFailed" value="<?php echo esc_attr($opt_StatusFailed); ?>" class="xxx" /> <?php esc_html_e('Failed','next-wc-orders'); ?>
        </td></tr>
    </table>    

    <h2 class="title"><?php esc_html_e('Stats','next-wc-orders'); ?></h2>
       
    <table class="form-table">
        <tr valign="top">
        <th scope="row"><?php esc_html_e('3D Pie','next-wc-orders'); ?></th>
        <td><input type="checkbox" name="optPie3D" value=1 <?php echo($opt_Pie3D==1?"checked ":"");?>class="wppd-ui-toggle" /> <?php esc_html_e('Displays stats pie chart in 3D','next-wc-orders'); ?>
        </td></tr>
    </table>   

    <table class="form-table">
    <tr valign="top">
    <th scope="row"><?php esc_html_e('Year colors','next-wc-orders'); ?></th>
    <td>
    <?php
    $listYears = explode("|",$tmpYears);
    $n = count($listYears)-1;
    for ($c=1;$c<$n;$c++)
        { $listY = explode("=",$listYears[$c]);
          $tmpOptPieYear = 'optPie'.$listY[0];
          echo '<input type="color" name="' . esc_attr($tmpOptPieYear) . '" value="' . esc_attr(${'opt_Pie'.$listY[0]}) . '" class="xxx" /> ' . esc_attr($listY[0]) . '<br>';
        }
    ?>
    </td></tr>
    </table>
        
    <?php submit_button(esc_html__('Save','next-wc-orders')); ?>
</form>
        <?php break;   
        
        
        
        
        
    
    case 'nwco_orders': ?> 
    <br>
<table><tr><td valign="top">
    <?php
    nwco_Orders_HTML($opt_Shipping,$selectedYear,$opt_Prod,$opt_Cat,$opt_Status,$opt_Total);
    ?>
</td><td valign="top">    
    <form method="post" action="options.php">
    <?php settings_fields('nwco-orders-group'); ?>
    <?php do_settings_sections('nwco-orders-group'); ?>

    <h2 class="title"><?php esc_html_e('Filter','next-wc-orders'); ?></h2>
    <table class="form-table">
        <tr valign="top">
        <th scope="row"><?php esc_html_e('Free shipping only','next-wc-orders'); ?></th>
        <td><input type="checkbox" id="optShipping" name="optShipping"  value=1 <?php echo($opt_Shipping==1?"checked ":"");?> class="wppd-ui-toggle" />
        </td></tr>

        <tr valign="top">
        <th scope="row"><?php esc_html_e('Year','next-wc-orders'); ?></th>
        <td><select id="optYear" name="optYear"><option value="">-</option>
        <?php
        $listYears = explode("|",$tmpYears);
        $n = count($listYears)-1;
        for ($c=1;$c<$n;$c++)
            { $listY = explode("=",$listYears[$c]);
              echo '<option ' . ($opt_Year==$listY[0]?'selected':'') . ' value="' . esc_attr($listY[0]) . '">' . esc_attr($listY[0]) . '</option>';
            }
        ?>
        </select>
        </td></tr>

        <tr valign="top">
        <th scope="row"><?php esc_html_e('Status','next-wc-orders'); ?></th>
        <td>
        <select name="optStatus" id="optStatus">
                <option value="">-</option>
                <option <?php echo ($opt_Status=='completed'?'selected':''); ?> value="completed">Completed</option>
                <option <?php echo ($opt_Status=='on-hold'?'selected':''); ?> value="on-hold">On-hold</option>
                <option <?php echo ($opt_Status=='processing'?'selected':''); ?> value="processing">Processing</option>
                <option <?php echo ($opt_Status=='pending'?'selected':''); ?> value="pending">Pending</option>
                <option <?php echo ($opt_Status=='refunded'?'selected':''); ?> value="refunded">Refunded</option>
                <option <?php echo ($opt_Status=='cancelled'?'selected':''); ?> value="cancelled">Cancelled</option>
                <option <?php echo ($opt_Status=='failed'?'selected':''); ?> value="failed">Failed</option>
        </select>
        </td></tr>
        
        <tr valign="top">
        <th scope="row"><?php esc_html_e('Total greater than','next-wc-orders'); ?></th>
        <td><input type="number" id="optTotal" name="optTotal" min="0" value="<?php echo esc_attr($opt_Total) ?>" class="xxx" /> <?php echo get_woocommerce_currency_symbol(); ?>
        <br><font color="#808080"><em><?php esc_html_e('Display only orders which total is greater than this amount','next-wc-orders'); ?></em></font></td></tr>
                
        <tr valign="top">
        <th scope="row"><?php esc_html_e('Product name','next-wc-orders'); ?></th>
        <td><input type="text" id="optProd" name="optProd" value="<?php echo esc_attr($opt_Prod) ?>" class="xxx" />
        </td></tr>

        <tr valign="top">
        <th scope="row"><?php esc_html_e('Product category','next-wc-orders'); ?></th>
        <td>
        
        <select id="optCat" name="optCat"><option value="">-</option>
        <?php
        $args = array(
          'taxonomy' => 'product_cat',
          'orderby' => 'name',
          'hierarchical' => 1,
          'hide_empty' => true,
          'parent'   => 0);
        $product_cat = get_terms($args);
        foreach ($product_cat as $parent_product_cat)
                { echo '<option ' . ($opt_Cat==$parent_product_cat->name?'selected':'') . ' value="' . esc_attr($parent_product_cat->name) . '">' . esc_attr($parent_product_cat->name) . '</option>';
                  $child_args = array(
                  'taxonomy' => 'product_cat',
                  'orderby' => 'name',
                  'hierarchical' => 1,
                  'hide_empty' => true,
                  'parent'   => $parent_product_cat->term_id);
                  $child_product_cats = get_terms($child_args);
                  
                  foreach ($child_product_cats as $child_product_cat)
                          { echo '<option ' . ($opt_Cat==$child_product_cat->name?'selected':'') . ' value="' . esc_attr($child_product_cat->name) . '"> - ' . esc_attr($child_product_cat->name) . '</option>';          
                            $sub_child_args = array(
                            'taxonomy' => 'product_cat',
                            'orderby' => 'name',
                            'hierarchical' => 1,
                            'hide_empty' => false, //true,
                            'parent'   => $child_product_cat->term_id);
                            $sub_child_product_cats = get_terms($sub_child_args);
                            
                            foreach ($sub_child_product_cats as $sub_child_product_cat)
                                    { echo '<option ' . ($opt_Cat==$sub_child_product_cat->name?'selected':'') . ' value="' . esc_attr($sub_child_product_cat->name) . '"> -- ' . esc_attr($sub_child_product_cat->name) . '</option>';          
                                    }
                          }
                }
        echo '</select>';
        ?>
        </td></tr>
    </table> 

<a href="#" onclick="document.getElementById('optShipping').checked=false; document.getElementById('optYear').value=''; document.getElementById('optStatus').value=''; document.getElementById('optTotal').value=''; document.getElementById('optProd').value=''; document.getElementById('optCat').value='';"><?php esc_html_e('Clear filter','next-wc-orders'); ?></a>
<?php
submit_button(esc_html__('Apply filter','next-wc-orders'));
?> 
</form>

<form method="post" action="admin.php?page=nwco-acp">
<input type="submit" name="do" value="<?php esc_html_e('Export CSV','next-wc-orders'); ?>" class="button" />
<input type="submit" name="do" value="<?php esc_html_e('Export HTML','next-wc-orders'); ?>" class="button" />
</form>
</td></tr></table>
    <?php break;
    
    
    
    
    
    
        
    case 'nwco_stats': ?> 
         <ul class="subsubsub">
		     <li><a href="/wp-admin/admin.php?page=nwco-acp&amp;tab=nwco_stats&amp;section=general" class="<?php echo ($section=="general"?"current":"")?>">General</a></li>
		     <?php
		     for ($c=1;$c<$n;$c++)
             { $listY = explode("=",$listYears[$c]);
               echo " | <li><a href=\"/wp-admin/admin.php?page=nwco-acp&amp;tab=nwco_stats&amp;section=" . esc_attr($listY[0]) . "\" class=\"" . ($section==$listY[0]?"current":"") . "\">" . esc_attr($listY[0]) . "</a></li>";
             }
         if (($section != "general") and ($section != "category"))
            echo " | <li><a href=\"/wp-admin/admin.php?page=nwco-acp&amp;tab=nwco_stats&amp;section=category&year=" . esc_attr($section) . "\" class=\"" . ($section=="category"?"current":"") . "\">" . esc_html__('Category','next-wc-orders') . "</a></li>";
         ?>
		     </ul><br class="clear">
		     <h2 class="title"><?php esc_html_e('Stats','next-wc-orders'); ?>
		     <?php if (($section != "general") and ($section != "category")) echo " [" . esc_attr($section) . "]";
		           if ($section == "category")
		              { $tmpYear = sanitize_text_field($_GET['year']);
		                echo " [" . esc_attr($tmpYear) . "]";
		              }
		     ?>
		     </h2>
         <?php
         switch($section)
               { case "general":
		                  ?>  
                      <table border="0">
                      <tr align="left" valign="top">
                      <th scope="row"><?php esc_html_e('Orders per year','next-wc-orders'); ?></th>
                      <?php nwco_Pie3D($tmpYears,"ORDERS"); ?>
                      </tr></table> 
                      <br>
                      <table border="0">
                      <tr align="left" valign="top">
                      <th scope="row"><?php esc_html_e('Total sales per year','next-wc-orders'); ?></th>
                      <?php nwco_Pie3D($tmpYears,"PAID"); ?>
                      </tr></table> 
                      <?php
                      break;
                 
                 case "category":
                      ?>
                      <table border="0">
                      <tr align="left" valign="top">
                      <th scope="row"><?php esc_html_e('Number of products by category','next-wc-orders'); ?></th>
                      <td align="center" valign="middle">
                      <?php $tmpYear = sanitize_text_field($_GET['year']);
                      nwco_StatsByCat($tmpYear);?>
                      </td></tr></table> 
                      <?php
                      break;
                      
                 default:
                 ?>
                 <table border="0">
                 <tr align="left" valign="top">
                 <th scope="row"><?php esc_html_e('Order status','next-wc-orders'); ?></th>
                 <?php nwco_Pie3D($tmpYears,"YEAR",$section); ?>
                 </tr></table> 
                 <?php
                 break;
               }
    break;

    default:
    break;
        } ?>
  </div>
</div>