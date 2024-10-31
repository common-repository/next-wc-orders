<?php
if (!defined('NWCO_KEY_DONATE'))
   { define('NWCO_KEY_DONATE','VERTFP5KUCWCE');
   }
if (!defined('NWCO_PLUGIN_NAME'))
   { define('NWCO_PLUGIN_NAME','Next Orders');
   }
if (!defined('NWCO_PLUGIN_SLUG'))
   { define('NWCO_PLUGIN_SLUG','next-wc-orders');
   }
if (!defined('NWCO_VERSION'))
   { define('NWCO_VERSION', '1.4.5');
   }
if (!defined('NWCO_TYPE'))
   { define('NWCO_TYPE', 'Free');
   }
if (!defined('NWCO_PLUGIN_PAGE'))
   { define('NWCO_PLUGIN_PAGE','nwco-acp');
   }
if (!defined('NWCO_MAX_YEARS'))
   { define('NWCO_MAX_YEARS', 10);
   }
function nwco_GetOrderYears()
{ $orders = wc_get_orders( array('numberposts' => -1) );
 
  $retYears = ""; $tmpCurYear = ""; 
  $tmpTotalOrders = 0; $tmpTotalPaid = 0;
  foreach($orders as $order)
         { $order_id = $order->get_id();
           $order = wc_get_order($order_id);
           $order_data = $order->get_data(); 
           $year_created = $order_data['date_created']->date('Y');
           if ($order_data['total'] > 0) 
              { if ($tmpCurYear != $year_created)
                   { if ($tmpCurYear != "")
                        { $retYears .= '|' . $tmpCurYear . "=" . $NbOrders . "/" . $tmpTotal;
                          $tmpTotalOrders += $NbOrders;
                          $tmpTotalPaid += $tmpTotal;
                        }
                     $NbOrders = 0;$tmpOrderId = "";$tmpTotal = 0;
                     $tmpCurYear = $year_created;
                   }
                $NbOrders++;
                $order_status = $order_data['status'];
                if (($order_status != 'refunded') and ($order_status != 'cancelled') and ($order_status != 'failed')) $tmpTotal += $order_data['total'];
                $tmpOrderId .= $order_id . "/";
              }
         }
  $retYears .= '|' . $tmpCurYear . "=" . $NbOrders . "/" . $tmpTotal . "|";
  $tmpTotalOrders += $NbOrders;
  $tmpTotalPaid += $tmpTotal;
  $retYears .= $tmpTotalOrders . "/" . $tmpTotalPaid;
  return $retYears;
}

function nwco_Orders_CSV($fd,$parShipping,$parYear,$parProd,$parCategory,$parStatus,$parTotal)
{ $currency_symbol = get_woocommerce_currency_symbol();
  $currencies    = get_woocommerce_currencies();
  $currency_code = get_woocommerce_currency();
  $strCur = $currency_code;
  
  $orders = wc_get_orders( array('numberposts' => -1) );

  $year_created_sav = '';
  $year_total = 0;
  $year_envio = 0;
  $year_prod = 0;

  $grand_total = 0;
  $grand_envio = 0;
  $grand_prod = 0;
  $tmpFoundGeneral = false;

  $opt_ShowOrderProducts = get_option('optShowOrderProducts');
  
  $opt_StatusCompleted = get_option('optStatusCompleted');   if ($opt_StatusCompleted == "") $opt_StatusCompleted = "#00ff00";
  $opt_StatusOnhold = get_option('optStatusOnhold');         if ($opt_StatusOnhold == "") $opt_StatusOnhold = "#ff7700";
  $opt_StatusProcessing = get_option('optStatusProcessing'); if ($opt_StatusProcessing == "") $opt_StatusProcessing = "#ff7700";
  $opt_StatusPending = get_option('optStatusPending');       if ($opt_StatusPending == "") $opt_StatusPending = "#ff7700";
  $opt_StatusRefunded = get_option('optStatusRefunded');     if ($opt_StatusRefunded == "") $opt_StatusRefunded = "#ff0000";
  $opt_StatusCancelled = get_option('optStatusCancelled');   if ($opt_StatusCancelled == "") $opt_StatusCancelled = "#ff0000";
  $opt_StatusFailed = get_option('optStatusFailed');         if ($opt_StatusFailed == "") $opt_StatusFailed = "#ff0000";

  foreach($orders as $order)
         { $order_id = $order->get_id();
           $order = wc_get_order( $order_id );

           $order_data = $order->get_data();
           $year_created = $order_data['date_created']->date('Y');

           if (($parYear == "") or ($parYear == $year_created))
              { $order_id = $order_data['id'];
                $order_parent_id = $order_data['parent_id'];
                $order_status = $order_data['status'];
                $order_currency = $order_data['currency'];
                $order_version = $order_data['version'];
                $order_payment_method = $order_data['payment_method'];
                $order_payment_method_title = $order_data['payment_method_title'];
                $order_payment_method = $order_data['payment_method'];
                $order_discount_total = $order_data['discount_total'];
                $order_discount_tax = $order_data['discount_tax'];
                $order_shipping_total = $order_data['shipping_total'];
                $order_shipping_tax = $order_data['shipping_tax'];

                if ((($parTotal == "") or ($order_data['total'] >= $parTotal)) and ($order_data['total'] > 0) and (($parShipping == "") or ($order_shipping_total == 0)) and (($parStatus == "") or ($parStatus == $order_status)))
                   { $tmpHeader = "";
                     if ($year_created_sav != $year_created)
                        { if ($year_created_sav != '') 
                             { if (($parProd == "") and ($parCategory == "")) $tmpHeader .= esc_html__('TOTAL','next-wc-orders') . ":;" . sprintf("%01.2f", $year_total) . $strCur . ";(" . sprintf("%01.2f", $year_prod) . " + " . sprintf("%01.2f", $year_envio)  . $strCur . ");\n";
                             }
                          $year_total = 0;
                          $year_created_sav = $year_created;
                          $year_envio = 0; $year_prod = 0;
                          //$tmpHeader .= "Order Id;Total (" . $strCur . ");Producto + Envio;" . $year_created . " - Fecha;Status\n";
                          $tmpHeader .= esc_html__('Order Id.','next-wc-orders') . ';' . esc_html__('Total','next-wc-orders') . ' (' . esc_attr($strCur) . ');' . esc_html__('Product + Shipping','next-wc-orders') . ';' . esc_attr($year_created) . ' -' . esc_html__('Date','next-wc-orders') . ';' . esc_html__('Status','next-wc-orders') . "\n";
                        }

                     $order_date_created = $order_data['date_created']->date('Y-m-d H:i:s');
                     $order_date_modified = $order_data['date_modified']->date('Y-m-d H:i:s');
                     
                     $order_total = $order_data['total'];
                     if ((($order_status != 'refunded') or ($parStatus == 'refunded')) and (($order_status != 'cancelled') or ($parStatus == 'cancelled')) and (($order_status != 'failed') or ($parStatus == 'failed')))
                        { $year_total += $order_total;
                          $year_envio += $order_shipping_total;
                          $grand_total += $order_total;
                          $grand_envio += $order_shipping_total;
 
                          $order_total_tax = $order_data['total_tax'];
                          $order_customer_id = $order_data['customer_id'];
    
                          $order_prod = $order_total - $order_shipping_total;
                          $year_prod += $order_prod;
                          $grand_prod += $order_prod;
                        }

                     $tmpDisplay = '';
                     $tmpDisplay .= $order_id . ';';
                     $tmpDisplay .= sprintf("%01.2f", $order_total) . $strCur . ";";

                     if ($order_shipping_total == 0)
                        $tmpDisplay .= sprintf("%01.2f", $order_prod) . " + " . esc_html__('Free','next-wc-orders') . ";";
                     else
                        $tmpDisplay .= sprintf("%01.2f", $order_prod) . " + " . sprintf("%01.2f", $order_shipping_total) . $strCur . ";";
                     $tmpDisplay .= $order_date_created . ';';
                     $tmpDisplay .= ucfirst($order_status) . "\n";
       
                     $tmpInd = 0;   
                     if (($opt_ShowOrderProducts) or ($parProd != "") or ($parCategory != ""))
                        { $tmpFoundProd = false;
                          $tmpFoundCat = false;
                          
                          $order = wc_get_order($order_id);
                          foreach ($order->get_items() as $item_key => $item_values)
                                  { $item_name = $item_values->get_name();
                                    $item_type = $item_values->get_type();
  
                                    $product_id = $item_values->get_product_id();
                                    $wc_product = $item_values->get_product();

                                     $item_data = $item_values->get_data();
                                     $product_name = $item_data['name'];
                                     $product_id = $item_data['product_id'];
                                     $variation_id = $item_data['variation_id'];
                                     $quantity = $item_data['quantity'];
                                     $tax_class = $item_data['tax_class'];
                                     $line_subtotal = $item_data['subtotal'];
                                     $line_subtotal_tax = $item_data['subtotal_tax'];
                                     $line_total = $item_data['total'];
                                     $line_total_tax = $item_data['total_tax'];
               
                                     if ($parCategory != "")
                                        { $term_names_array = wp_get_post_terms($product_id, 'product_cat', array('fields' => 'names') );
                                          $term_names_string = count($term_names_array) > 0 ? implode(', ', $term_names_array) : '';

                                          $posCat = strpos($term_names_string, $parCategory);
                                          if ($posCat !== false)
                                             { $tmpFoundCat = true;
                                             }
                                        }
                                      
                                     if ($parProd != "")
                                        { $pos = strpos($product_name, $parProd);
                                          if ($pos !== false)
                                             { $tmpFoundProd = true;
                                             }
                                        }
                          
                                     if ((($parProd == "") or ($tmpFoundProd)) and (($parCategory == "") or ($tmpFoundCat)))  
                                        { $product_price = $line_total / $quantity;
                                          if ($opt_ShowOrderProducts)
                                             { $tmpInd ++;

                                               if ($opt_ShowOrderProducts) $tmpDisplay .= "• " . utf8_decode($item_name) . ";" .$quantity . "x" . $product_price . $strCur . "=" . $line_total . $strCur . "\n";
                                           }
                                        }
                                   $tmpFoundProd = false;
                                   $tmpFoundCat = false;
                                  }
                        }
                    
                     if ($tmpInd != 0)
                        { $tmpFoundGeneral = true;
                          fwrite($fd,$tmpHeader);
                          fwrite($fd,$tmpDisplay);
                        }
                   }
              }
         }

  if ($tmpFoundGeneral)
     { $strStatus = "";
       if ($parStatus != "") $strStatus = '[' . $parStatus . '] - ';
       if (($parProd == "") and ($parCategory == "")) fwrite($fd,esc_html__('TOTAL','next-wc-orders') . ";" . sprintf("%01.2f", $year_total) . $strCur . ";" . sprintf("%01.2f", $year_prod) . " + " . sprintf("%01.2f", $year_envio)  . $strCur . "\n");
     }
     
  if ($tmpFoundGeneral)
     if ($parYear == "") fwrite($fd,"\n" . esc_html__('GRAND TOTAL','next-wc-orders') . ";" . $grand_total . $strCur . ";" . sprintf("%01.2f", $grand_prod) . ' + ' . sprintf("%01.2f", $grand_envio)  . $strCur . "\n");
}

function nwco_Orders_HTML($parShipping,$parYear,$parProd,$parCategory,$parStatus,$parTotal)
{ $gCur = get_woocommerce_currency_symbol();
  $orders = wc_get_orders( array('numberposts' => -1) );
  $year_created_sav = '';
  $year_total = 0;
  $year_envio = 0;
  $year_prod = 0;

  $grand_total = 0;
  $grand_envio = 0;
  $grand_prod = 0;
  $tmpFoundGeneral = false;
  
  $opt_ShowOrderProducts = get_option('optShowOrderProducts');
  
  $opt_StatusCompleted = get_option('optStatusCompleted');   if ($opt_StatusCompleted == "") $opt_StatusCompleted = "#00ff00";
  $opt_StatusOnhold = get_option('optStatusOnhold');         if ($opt_StatusOnhold == "") $opt_StatusOnhold = "#ff7700";
  $opt_StatusProcessing = get_option('optStatusProcessing'); if ($opt_StatusProcessing == "") $opt_StatusProcessing = "#ff7700";
  $opt_StatusPending = get_option('optStatusPending');       if ($opt_StatusPending == "") $opt_StatusPending = "#ff7700";
  $opt_StatusRefunded = get_option('optStatusRefunded');     if ($opt_StatusRefunded == "") $opt_StatusRefunded = "#ff0000";
  $opt_StatusCancelled = get_option('optStatusCancelled');   if ($opt_StatusCancelled == "") $opt_StatusCancelled = "#ff0000";
  $opt_StatusFailed = get_option('optStatusFailed');         if ($opt_StatusFailed == "") $opt_StatusFailed = "#ff0000";
  
  echo "<table cellpadding=2 cellspacing=0 border=1>";
  foreach($orders as $order)
         { $order_id = $order->get_id();
           $order = wc_get_order($order_id);

          $order_data = $order->get_data();
          $year_created = $order_data['date_created']->date('Y');

          if (($parYear == "") or ($parYear == $year_created)) 
             { $order_id = $order_data['id'];
               $order_parent_id = $order_data['parent_id'];
               $order_status = $order_data['status'];
               $order_currency = $order_data['currency'];
               $order_version = $order_data['version'];
               $order_payment_method = $order_data['payment_method'];
               $order_payment_method_title = $order_data['payment_method_title'];
               $order_payment_method = $order_data['payment_method'];
               $order_discount_total = $order_data['discount_total'];
               $order_discount_tax = $order_data['discount_tax'];
               $order_shipping_total = $order_data['shipping_total'];
               $order_shipping_tax = $order_data['shipping_tax'];

               if ((($parTotal == "") or ($order_data['total'] >= $parTotal)) and ($order_data['total'] > 0) and (($parShipping == "") or ($order_shipping_total == 0)) and (($parStatus == "") or ($parStatus == $order_status)))
                  { if ($year_created_sav != $year_created)
                       { if ($year_created_sav != '') 
                            { if (($parProd == "") and ($parCategory == "")) echo '<tr style="background-color: #2271B1; color: white"><td colspan=6><b>' . esc_html__('TOTAL','next-wc-orders') . ' (' . esc_attr($year_created_sav) . '): ' . sprintf("%01.2f", esc_attr($year_total)) . esc_attr($gCur) . '</b> (' . sprintf("%01.2f", esc_attr($year_prod)) . ' + ' . sprintf("%01.2f", esc_attr($year_envio))  . esc_attr($gCur) . ')</td></tr>';
                            }
                         $year_total = 0;
                         $year_created_sav = $year_created;
                         $year_envio = 0; $year_prod = 0;
                         echo '<tr style="background-color: #2271B1; color: white"><td align="center"><b>' . esc_html__('Order Id.','next-wc-orders') . '</b></td><td align="center"><b>' . esc_html__('Total','next-wc-orders') . ' (' . esc_attr($gCur) . ')</b></td><td align="center"><b>' . esc_html__('Product + Shipping','next-wc-orders') . '</b></td><td align="center"><b>' . esc_attr($year_created) . ' - ' . esc_html__('Date','next-wc-orders') . '</b></td><td align="center"><b>' . esc_html__('Status','next-wc-orders') . '</b></td></tr>';
                       }
   
                    $order_date_created = $order_data['date_created']->date('Y-m-d H:i:s');
                    $order_date_modified = $order_data['date_modified']->date('Y-m-d H:i:s');

                    $order_total = $order_data['total'];
                    if ((($order_status != 'refunded') or ($parStatus == 'refunded')) and (($order_status != 'cancelled') or ($parStatus == 'cancelled')) and (($order_status != 'failed') or ($parStatus == 'failed')))
                       { $year_total += $order_total;
                         $year_envio += $order_shipping_total;
                         $grand_total += $order_total;
                         $grand_envio += $order_shipping_total;

                         $order_total_tax = $order_data['total_tax'];
                         $order_customer_id = $order_data['customer_id'];
      
                         $order_prod = $order_total - $order_shipping_total;
                         $year_prod += $order_prod;
                         $grand_prod += $order_prod;
                       }

                    $tmpInd = 0;                  
                    if (($opt_ShowOrderProducts) or ($parProd != "") or ($parCategory != ""))
                       { $tmpFoundProd = false;
                         $tmpFoundCat = false;

                         $order = wc_get_order($order_id);
                         foreach ($order->get_items() as $item_key => $item_values)
                                 { $item_name = $item_values->get_name();
                                   $item_type = $item_values->get_type();
  
                                   $product_id = $item_values->get_product_id();
                                   $wc_product = $item_values->get_product();

                                   $item_data = $item_values->get_data();
                                   $product_name = $item_data['name'];
                                   $product_id = $item_data['product_id'];
                                   $variation_id = $item_data['variation_id'];
                                   $quantity = $item_data['quantity'];
                                   $tax_class = $item_data['tax_class'];
                                   $line_subtotal = $item_data['subtotal'];
                                   $line_subtotal_tax = $item_data['subtotal_tax'];
                                   $line_total = $item_data['total'];
                                   $line_total_tax = $item_data['total_tax'];

                                   if ($parCategory != "")
                                      { $term_names_array = wp_get_post_terms($product_id, 'product_cat', array('fields' => 'names') );
                                        $term_names_string = count($term_names_array) > 0 ? implode(', ', $term_names_array) : '';
                                        $posCat = strpos($term_names_string, $parCategory);
                                        if ($posCat !== false)
                                           { $tmpFoundCat = true;
                                           }
                                      }

                                   if ($parProd != "")
                                      { $pos = strpos($product_name, $parProd);
                                        if ($pos !== false) 
                                           { $tmpFoundProd = true;
                                           }
                                      }
                                      
                                   if ((($parProd == "") or ($tmpFoundProd)) and (($parCategory == "") or ($tmpFoundCat)))
                                      { $product_price = $line_total / $quantity;
                                        if ($opt_ShowOrderProducts)
                                           { $tmpInd ++;
                                             $A_ProdId[$tmpInd] = $product_id;
                                             $A_ProdName[$tmpInd] = $item_name;
                                             $A_ProdQuantity[$tmpInd] = $quantity;
                                             $A_ProdPrice[$tmpInd] = $product_price;
                                             $A_ProdTotal[$tmpInd] = $line_total;
                                           }
                                      }
                                   $tmpFoundProd = false;
                                   $tmpFoundCat = false;
                                 }
                       }
        
                    if ($tmpInd != 0)
                       { $tmpFoundGeneral = true;
                         echo '<tr><td><em><a target="_wOrder" href="post.php?post=' . esc_attr($order_id) . '&action=edit">' . esc_attr($order_id) . '</a></em></td>';
                         echo '<td><b>' . sprintf("%01.2f", esc_attr($order_total)) . esc_attr($gCur) . '</b></td>';
                         if ($order_shipping_total == 0)
                            echo '<td>' . sprintf("%01.2f", esc_attr($order_prod)) . esc_attr($gCur) . ' + ' . esc_html__('Free','next-wc-orders') . '</td>';
                         else
                            echo '<td>' . sprintf("%01.2f", esc_attr($order_prod)) . esc_attr($gCur) . ' + ' . sprintf("%01.2f", esc_attr($order_shipping_total)) . esc_attr($gCur) . '</td>';
                         echo '<td>' . esc_attr($order_date_created) . '</td>';

                         switch ($order_status)
                           { case 'completed':  $tmpBackColor = $opt_StatusCompleted;  break;
                             case 'on-hold':    $tmpBackColor = $opt_StatusOnhold;     break;
                             case 'processing': $tmpBackColor = $opt_StatusProcessing; break;
                             case 'pending':    $tmpBackColor = $opt_StatusPending;    break;
                             case 'refunded':   $tmpBackColor = $opt_StatusRefunded;   break;
                             case 'cancelled':  $tmpBackColor = $opt_StatusCancelled;  break;
                             case 'failed':     $tmpBackColor = $opt_StatusFailed;     break;
                             default:           $tmpBackColor = $opt_StatusCompleted;  break;
                           }
                         echo '<td style="color:' . esc_attr($tmpBackColor) . '">' . ucfirst(esc_attr($order_status)) . '</td></tr>';
                    
                         if ($opt_ShowOrderProducts) echo '<tr><td colspan=5>';             
                         for ($o=1;$o<=$tmpInd;$o++)
                             { echo '<b>&bull; <a target="_wProd" href=post.php?post=' . esc_attr($A_ProdId[$o]) . '&action=edit>' . esc_attr($A_ProdName[$o]) .'</a></b>, ' . esc_attr($A_ProdQuantity[$o]) . 'x' . esc_attr($A_ProdPrice[$o]) . esc_attr($gCur) . ' =' . esc_attr($A_ProdTotal[$o]) . esc_attr($gCur) . '<br>';
                             }
                         if ($opt_ShowOrderProducts) echo '</td></tr>';
                       }
                  }
             }
         }

  if ($tmpFoundGeneral)
     { $strStatus = "";
       if ($parStatus != "") $strStatus = '[' . $parStatus . '] - ';
       if (($parProd == "") and ($parCategory == "")) echo '<tr style="background-color: #2271B1; color: white"><td colspan=6>' . esc_attr($strStatus)  . '<b>' . esc_html__('TOTAL','next-wc-orders') . ' (' . esc_attr($year_created_sav) . '): ' . sprintf("%01.2f", esc_attr($year_total)) . esc_attr($gCur) . '</b> (' . sprintf("%01.2f", esc_attr($year_prod)) . ' + ' . sprintf("%01.2f", esc_attr($year_envio))  . esc_attr($gCur) . ')</td></tr>';
     }
  else
     echo '<tr bgcolor="#ff0000"><td colspan=6>' . esc_html__('No match found!!!','next-wc-orders') . '</td></tr>';   
  echo "</table>";

  if ($tmpFoundGeneral)
     { echo '<br>';
       if ($parYear == "") echo '<b>' . esc_html__('GRAND TOTAL','next-wc-orders') . ': ' . esc_attr($grand_total) . esc_attr($gCur) . '</b> (' . sprintf("%01.2f", esc_attr($grand_prod)) . ' + ' . sprintf("%01.2f", esc_attr($grand_envio))  . esc_attr($gCur) . ')<br><br>';
     }
}

add_action('admin_enqueue_scripts', 'nwco_Styles');
function nwco_Styles()
{ $tmpStr = plugins_url('/',__FILE__);
  if (substr($tmpStr,-1) == "/")
     $tmpPos = strrpos($tmpStr,'/',-2);
  else   
     $tmpPos = strrpos($tmpStr,'/',-1);
  $tmpStr = substr($tmpStr,0,$tmpPos);
  $tmpPathCSS = $tmpStr . '/css/style.css';

  wp_enqueue_style('nwco_style_css', $tmpPathCSS);
}

add_action('plugins_loaded', 'nwco_checkVersion');
function nwco_CheckVersion()
{ $tmpCurVersion = get_option('nwcoCurrentVersion');
  $tmpCurType = get_option('nwcoCurrentType');
  if((version_compare($tmpCurVersion, NWCO_VERSION, '<')) or (NWCO_TYPE !== $tmpCurType))
    { nwco_PluginActivation();
    }
}

function nwco_PluginActivation()
{ update_option('nwcoCurrentVersion', NWCO_VERSION);
  update_option('nwcoCurrentType', NWCO_TYPE);
  
  return NWCO_VERSION;
}
register_activation_hook(__FILE__, 'nwco_PluginActivation');

add_action( 'admin_menu','nwco_Add_Menu');
function nwco_Add_Menu()
{ add_menu_page(
      'Next Orders for WooCommerce',
      NWCO_PLUGIN_NAME,
      'manage_options',
      'nwco-acp',
      'nwco_acp_callback',
      plugins_url(NWCO_PLUGIN_SLUG . '/images/icon.png')
    );
  
  add_submenu_page('nwco-acp', __('Settings','next-wc-product-orders'), __('Settings','next-wc-orders'), 'manage_options', 'nwco-acp&tab=nwco_settings', 'render_generic_settings_page');
  add_submenu_page('nwco-acp', __('Orders','next-wc-product-orders'), __('Orders','next-wc-product-orders'), 'manage_options', 'nwco-acp&tab=nwco_orders', 'render_generic_settings_page');
  add_submenu_page('nwco-acp', __('Stats','next-wc-product-orders'), __('Stats','next-wc-product-orders'), 'manage_options', 'nwco-acp&tab=nwco_stats', 'render_generic_settings_page');

	add_action('admin_init','register_nwco_settings');  
}

add_action('init','nwco_load_textdomain');
function nwco_load_textdomain()
{ load_plugin_textdomain('next-wc-orders',false,NWCO_PLUGIN_SLUG . '/languages/'); 
}

function register_nwco_settings()
{ register_setting('nwco-settings-group','nwcoCurrentVersion');
  register_setting('nwco-settings-group','nwcoCurrentType');
  
  register_setting('nwco-settings-group','optShowOrderProducts');
  register_setting('nwco-settings-group','optStatusCompleted');
  register_setting('nwco-settings-group','optStatusOnhold');
  register_setting('nwco-settings-group','optStatusProcessing');
  register_setting('nwco-settings-group','optStatusPending');
  register_setting('nwco-settings-group','optStatusRefunded');
  register_setting('nwco-settings-group','optStatusCancelled');
  register_setting('nwco-settings-group','optStatusFailed');
  register_setting('nwco-settings-group','optPie3D');
  
  $tmpYears = nwco_GetOrderYears();
  $listYears = explode("|",$tmpYears);
  $n = count($listYears)-1;
  for ($c=1;$c<$n;$c++)
      { $listY = explode("=",$listYears[$c]);
        $tmpOptPieYear = 'optPie'.$listY[0];
        register_setting('nwco-settings-group',$tmpOptPieYear);
      }
      
  register_setting('nwco-orders-group','optShipping');
	register_setting('nwco-orders-group','optYear');
	register_setting('nwco-orders-group','optProd');
	register_setting('nwco-orders-group','optCat');
	register_setting('nwco-orders-group','optTotal');
	register_setting('nwco-orders-group','optStatus');
}

function nwco_acp_callback()
{ global $title;

  if (!current_user_can('administrator'))
     { wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	   }
	
  print '<div class="wrap">';
  print "<h1 class=\"stabilo\">$title</h1><hr>";

  $file = plugin_dir_path( __FILE__ ) . "nwco-acp-page.php";
  if (file_exists($file))
      require $file;

  echo "<p><em><b>" . esc_html__('Add for free','next-wc-orders') . " <a target=\"_blank\" href=\"https://nxt-web.com/wordpress-plugins/\" style=\"color:#FE5500;font-weight:bold;font-size:1.2em\">" . esc_html__('Next Product Labels & Badges for WooCommerce','next-wc-orders') .  " & " . esc_html__('Next Toolbox for WooCommerce','next-wc-orders') . "</a></b></em></p>";
  echo "<p><em><b>" . esc_html__('You like this plugin?','next-wc-orders') . " <a target=\"_blank\" href=\"https://www.paypal.com/donate/?hosted_button_id=" . NWCO_KEY_DONATE . "\" style=\"color:#FE5500;font-weight:bold;font-size:1.2em\">" . esc_html__('Offer me a coffee!','next-wc-orders') . "</a></b></em>";
  $CoffeePath = plugin_dir_url( dirname( __FILE__ ) )  . '/images/coffee-donate.gif';
  echo '&nbsp;<img src="' . esc_attr($CoffeePath) . '"></p>';
  print '</div>';
}

function nwco_Pie3D($parOrderPerYear,$parType,$parYear="")
{ $PieHeight = 220;$PieWidth = 220;
  $BarHeight = 220;$BarWidth = 380;
  $opt_Pie3D = get_option('optPie3D');
  if ($opt_Pie3D) $PieHeight = $PieHeight/2+30;
  $tmpCur = get_woocommerce_currency_symbol();

  $imagePie = imagecreatetruecolor($PieWidth,$PieHeight);
  $transparent_color = imagecolorallocate($imagePie, 0xff, 0xff, 0xff);
  imagefill($imagePie,0,0,$transparent_color);
  imagecolortransparent($imagePie,$transparent_color);
  
  $imageBar = imagecreatetruecolor($BarWidth,$BarHeight);
  $transparent_color = imagecolorallocate($imageBar, 0xff, 0xff, 0xff);
  imagefill($imageBar,0,0,$transparent_color);
  imagecolortransparent($imageBar,$transparent_color);
              
  if (($parType == "ORDERS") or ($parType == "PAID"))
     { //--- Set basic colors for chart:
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
       $tmpLimit = NWCO_MAX_YEARS;
       
       $tmpTotalY = 0;
       $listY_Anterior = 0;
  
       $listYears = explode("|",$parOrderPerYear);

       $n = count($listYears)-1;
       if ($n > NWCO_MAX_YEARS) $n = NWCO_MAX_YEARS;
       for ($c=1;$c<$n;$c++)
           { $listY = explode("=",$listYears[$c]);
             $tmpOptPie = 'optPie'.$listY[0];
             ${'opt_Pie'.$listY[0]} = get_option($tmpOptPie);
             if (${'opt_Pie'.$listY[0]} != "") $A_BasicColor[$c] = ${'opt_Pie'.$listY[0]};
           }
       $tmpLast = explode("/",$listYears[$c]);
       $tmpTotalYear = $tmpLast[0];
       $tmpTotalPaid = $tmpLast[1];

       for($i=1;$i<=NWCO_MAX_YEARS;$i++)
          { list($red,$green,$blue) = sscanf($A_BasicColor[$i],"#%02x%02x%02x");
            $A_PieColor[$i] = imageColorAllocate($imagePie,$red,$green,$blue);

            $tmpColorDarker = nwco_DarkenColor($A_BasicColor[$i]);
            list($red,$green,$blue) = sscanf($tmpColorDarker,"#%02x%02x%02x");
            $A_PieColorDarken[$i] = imageColorAllocate($imagePie,$red,$green,$blue);
          }
     }
  else
     { $opt_StatusCompleted = get_option('optStatusCompleted');   if ($opt_StatusCompleted == "") $opt_StatusCompleted = "#00ff00";
       $opt_StatusOnhold = get_option('optStatusOnhold');         if ($opt_StatusOnhold == "") $opt_StatusOnhold = "#ff7700";
       $opt_StatusProcessing = get_option('optStatusProcessing'); if ($opt_StatusProcessing == "") $opt_StatusProcessing = "#ff7700";
       $opt_StatusPending = get_option('optStatusPending');       if ($opt_StatusPending == "") $opt_StatusPending = "#ff7700";
       $opt_StatusRefunded = get_option('optStatusRefunded');     if ($opt_StatusRefunded == "") $opt_StatusRefunded = "#ff0000";
       $opt_StatusCancelled = get_option('optStatusCancelled');   if ($opt_StatusCancelled == "") $opt_StatusCancelled = "#ff0000";
       $opt_StatusFailed = get_option('optStatusFailed');         if ($opt_StatusFailed == "") $opt_StatusFailed = "#ff0000";
                
       $NB_STATUS = 7;
       $A_BasicColor[1] = $opt_StatusCompleted;
       $A_BasicColor[2] = $opt_StatusOnhold;
       $A_BasicColor[3] = $opt_StatusProcessing;
       $A_BasicColor[4] = $opt_StatusPending;
       $A_BasicColor[5] = $opt_StatusRefunded;
       $A_BasicColor[6] = $opt_StatusCancelled;
       $A_BasicColor[7] = $opt_StatusFailed;
       $tmpLimit = $NB_STATUS;
     }

  for($i=1;$i<=$tmpLimit;$i++)
     { list($red,$green,$blue) = sscanf($A_BasicColor[$i],"#%02x%02x%02x");
       $A_PieColor[$i] = imageColorAllocate($imagePie,$red,$green,$blue);

       $tmpColorDarker = nwco_DarkenColor($A_BasicColor[$i]);
       list($red,$green,$blue) = sscanf($tmpColorDarker,"#%02x%02x%02x");
       $A_PieColorDarken[$i] = imageColorAllocate($imagePie,$red,$green,$blue);
     }
          
  echo '<td align="center" valign="top">';
  echo '<table cellpadding="2" cellspacing="0" border="1">';
  switch ($parType)
         { case "ORDERS":
                echo '<tr><td align=center><b>' . esc_html__('Year','next-wc-orders') . '</b></td><td><b>' . esc_html__('Number of orders','next-wc-orders') . '</b></td><td><b><font color="#c0c0c0">' . esc_html__('Evolution','next-wc-orders') . '</font></b></td></tr>';
                for ($c=1;$c<$n;$c++)
                    { $listAll = explode("=",$listYears[$c]);
                      $listY = explode("/",$listAll[1]);
                      $tmpEvol = round(100*($listY[0]-$listY_Anterior)/$listY_Anterior,2);
                      echo '<tr><td align=center bgcolor="' . esc_attr($A_BasicColor[$c]) . '"><b>' . esc_attr($listAll[0]) . '</b></td><td align=right>'. esc_attr($listY[0]) . ' (' . round(esc_attr($listY[0])*100/esc_attr($tmpTotalYear),2) . '%)</td>';
                      $A_Years[$c] = $listAll[0];
                      if ($listY_Anterior == 0)
                         echo '<td>-</td></tr>';
                      else
                         { if ($tmpEvol <= 0)
                              echo '<td><font color="#c00000">' . esc_attr($tmpEvol) . '%</font></td></tr>';
                            else
                              echo '<td><font color="#008000">+' . esc_attr($tmpEvol) . '%</font></td></tr>';
                         }
                      $listY_Anterior = $listY[0];
                      $A_ChartVal[$c] = $listY[0];
                      $tmpTotalY += $listY[0];
                    }
                echo "<tr><td align=center><b>" . esc_html__('TOTAL','next-wc-orders') . "</b></td><td align=right><b>" . esc_attr($tmpTotalY) . "</b></td><td>&nbsp;</td></tr>";
                echo "</table><em>" . esc_html__('Average number of orders per year:','next-wc-orders') . " " . esc_attr($tmpTotalY/($n-1)) . "</em>";
                break;
                
           case "PAID":
                echo '<tr><td align=center><b>' . esc_html__('Year','next-wc-orders') . '</b></td><td align=center><b>' . esc_html__('Total sales','next-wc-orders') . ' (' . esc_attr($tmpCur) . ')</b></td><td><b><font color="#c0c0c0">' . esc_html__('Evolution','next-wc-orders') . '</font></b></td></tr>';
                for ($c=1;$c<$n;$c++)
                    { $listAll = explode("=",$listYears[$c]);
                      $listY = explode("/",$listAll[1]);
                      $tmpEvol = round(100*($listY[1]-$listY_Anterior)/$listY_Anterior,2);
                      echo '<tr><td align=center bgcolor="' . esc_attr($A_BasicColor[$c]) . '"><b>' . esc_attr($listAll[0]) . '</b></td><td>'. esc_attr($listY[1]) . esc_attr($tmpCur) . ' (' . round(esc_attr($listY[1])*100/esc_attr($tmpTotalPaid),2) . '%)</td>';
                      $A_Years[$c] = $listAll[0];
                      if ($listY_Anterior == 0)
                         echo '<td>-</td></tr>';
                      else
                         { if ($tmpEvol <= 0)
                              echo '<td><font color="#c00000">' . esc_attr($tmpEvol) . '%</font></td></tr>';
                            else
                              echo '<td><font color="#008000">+' . esc_attr($tmpEvol) . '%</font></td></tr>';
                         }
                      $listY_Anterior = $listY[1];
                      $A_ChartVal[$c] = $listY[1];
                      $tmpTotalY += $listY[1];
                    }
                echo "<tr><td align=center><b>" . esc_html__('TOTAL','next-wc-orders') . "</b></td><td colspan=2><b>" . esc_attr($tmpTotalY) . esc_attr($tmpCur) . "</b></td></tr>";
                echo "</table><em>" . esc_html__('Average amount per year:','next-wc-orders') . " " . esc_attr($tmpTotalY/($n-1)) . esc_attr($tmpCur) . "</em>";
                break;
                
           case "YEAR":
                $orders = wc_get_orders( array('numberposts' => -1) );

                $tmpNbStatusCompleted = 0;
                $tmpNbStatusOnhold = 0;
                $tmpNbStatusProcessing = 0;
                $tmpNbStatusPending = 0;
                $tmpNbStatusRefunded = 0;
                $tmpNbStatusCancelled = 0;
                $tmpNbStatusFailed = 0;

                $tmpTotalOrders = 0;
                $tmpTotalSales = 0;
                for ($m=1;$m<=12;$m++)
                    { $A_MonthOrders[$m] = 0;
                      $A_MonthSales[$m] = 0;
                    }
                foreach($orders as $order)
                       { $order_id = $order->get_id();
                         $order = wc_get_order($order_id);

                         $order_data = $order->get_data();
                         $year_created = $order_data['date_created']->date('Y');

                         if (($parYear == $year_created) and ($order_data['total'] > 0))
                            { $month_created = $order_data['date_created']->date('m');
                              $A_MonthOrders[(int)$month_created]++;

                              $A_MonthSales[(int)$month_created] += $order_data['total'];
                              $tmpTotalSales += $order_data['total'];

                              $order_status = $order_data['status'];
                              switch ($order_status)
                                     { case 'completed':  $tmpNbStatusCompleted++;  break;
                                       case 'on-hold':    $tmpNbStatusOnhold++;     break;
                                       case 'processing': $tmpNbStatusProcessing++; break;
                                       case 'pending':    $tmpNbStatusPending++;    break;
                                       case 'refunded':   $tmpNbStatusRefunded++;   break;
                                       case 'cancelled':  $tmpNbStatusCancelled++;  break;
                                       case 'failed':     $tmpNbStatusFailed++;     break;
                                       default:                                     break;
                                     }
                              $tmpTotalOrders++;
                            }
                       }
                       
                echo '<tr><td align=center><b>' . esc_html__('Status','next-wc-orders') . '</b></td><td><b>' . esc_html__('Total orders','next-wc-orders') . '</b></td></tr>';
                if ($tmpNbStatusCompleted != 0) echo '<tr><td align=center bgcolor="' . esc_attr($opt_StatusCompleted) . '"><b>' . esc_html__('Completed','next-wc-orders') . '</b></td><td align=right>'. esc_attr($tmpNbStatusCompleted) . ' (' . round(esc_attr($tmpNbStatusCompleted)*100/esc_attr($tmpTotalOrders),2) . '%)</td></tr>';
                if ($tmpNbStatusOnhold != 0) echo '<tr><td align=center bgcolor="' . esc_attr($opt_StatusOnhold) . '"><b>' . esc_html__('On hold','next-wc-orders') . '</b></td><td align=right>'. esc_attr($tmpNbStatusOnhold) . ' (' . round(esc_attr($tmpNbStatusOnhold)*100/esc_attr($tmpTotalOrders),2) . '%)</td></tr>';
                if ($tmpNbStatusProcessing != 0) echo '<tr><td align=center bgcolor="' . esc_attr($opt_StatusProcessing) . '"><b>' . esc_html__('Processing','next-wc-orders') . '</b></td><td align=right>'. esc_attr($tmpNbStatusProcessing) . ' (' . round(esc_attr($tmpNbStatusProcessing)*100/esc_attr($tmpTotalOrders),2) . '%)</td></tr>';
                if ($tmpNbStatusPending != 0) echo '<tr><td align=center bgcolor="' . esc_attr($opt_StatusPending) . '"><b>' . esc_html__('Pending','next-wc-orders') . '</b></td><td align=right>'. esc_attr($tmpNbStatusPending) . ' (' . round(esc_attr($tmpNbStatusPending)*100/esc_attr($tmpTotalOrders),2) . '%)</td></tr>';
                if ($tmpNbStatusRefunded != 0) echo '<tr><td align=center bgcolor="' . esc_attr($opt_StatusRefunded) . '"><b>' . esc_html__('Refunded','next-wc-orders') . '</b></td><td align=right>'. esc_attr($tmpNbStatusRefunded) . ' (' . round(esc_attr($tmpNbStatusRefunded)*100/esc_attr($tmpTotalOrders),2) . '%)</td></tr>';
                if ($tmpNbStatusCancelled != 0) echo '<tr><td align=center bgcolor="' . esc_attr($opt_StatusCancelled) . '"><b>' . esc_html__('Cancelled','next-wc-orders') . '</b></td><td align=right>'. esc_attr($tmpNbStatusCancelled) . ' (' . round(esc_attr($tmpNbStatusCancelled)*100/esc_attr($tmpTotalOrders),2) . '%)</td></tr>';
                if ($tmpNbStatusFailed != 0) echo '<tr><td align=center bgcolor="' . esc_attr($opt_StatusFailed) . '"><b>' . esc_html__('Failed','next-wc-orders') . '</b></td><td align=right>'. esc_attr($tmpNbStatusFailed) . ' (' . round(esc_attr($tmpNbStatusFailed)*100/esc_attr($tmpTotalOrders),2) . '%)</td></tr>';
                echo "<tr><td align=center><b>" . esc_html__('TOTAL','next-wc-orders') . "</b></td><td align=right><b>" . esc_attr($tmpTotalOrders) . "</b></td></tr>";
                echo '</table>';
                break;
                
           default:           
         }
  echo '</td>'; 
        
  if (($parType == "ORDERS") or ($parType == "PAID"))
     { for ($c=1;$c<$n;$c++)
           { $A_PiePerc[$c] = $A_ChartVal[$c] * 100 / $tmpTotalY;
           }
          
       $NbYears = $n-1;
       if ($NbYears > NWCO_MAX_YEARS) $NbYears = NWCO_MAX_YEARS; 
       $CenterX = $PieWidth/2;$CenterY = $PieHeight/2;
       if ($opt_Pie3D) $CenterY = $PieHeight/2-15;
       $ArcWidth = 200;$ArcHeight = 200;

       if ($opt_Pie3D)
          { $ArcHeight = $ArcHeight / 2;
            $Pie3DBorder = 30;
            $tmpFrom = 0; $tmpTo = 0;
            for ($c=$CenterY + $Pie3DBorder;$c>$CenterY;$c--)
                { for ($i=1;$i<=$NbYears;$i++)
                      { if ($A_PiePerc[$i] != 0)
                           { $tmpNewTo = $tmpTo + $A_PiePerc[$i]*3.6;
                             $tmpFrom = $tmpTo;
                             $tmpTo = $tmpNewTo;
                             imagefilledarc($imagePie,$CenterX,$c,$ArcWidth,$ArcHeight,$tmpFrom,$tmpTo,$A_PieColorDarken[$i],IMG_ARC_PIE);
                           }
                      }
                }
          }

       $tmpFrom = 0; $tmpTo = 0;
       for ($i=1;$i<=$NbYears;$i++)
           { if ($A_PiePerc[$i] != 0)
                { $tmpNewTo = $tmpTo + $A_PiePerc[$i]*3.6;
                  $tmpFrom = $tmpTo;
                  $tmpTo = $tmpNewTo;
                  imagefilledarc($imagePie,$CenterX,$CenterY,$ArcWidth,$ArcHeight,$tmpFrom,$tmpTo,$A_PieColor[$i],IMG_ARC_PIE);
                }
           }

       echo '<td align="center" valign="top">';
       ob_start();
       imagepng($imagePie);
       $img = ob_get_clean();
       echo "<img src='data:image/gif;base64," . base64_encode($img) . "'><br>";
       imagedestroy($imagePie);
       
       $tmpMax = 0;
       $FontHeight = 30;
       for ($c=1;$c<$n;$c++)
           { if ($A_ChartVal[$c] > $tmpMax) $tmpMax = $A_ChartVal[$c];
           }
       for ($c=1;$c<$n;$c++)
           { $A_Bar[$c] = $A_ChartVal[$c] * ($BarHeight-$FontHeight-10) / $tmpMax;
           }
                   
       $upload_path = wp_upload_dir();     
       $path = $upload_path['basedir'];
       $FontPath = plugin_dir_path( __DIR__ ) . "fonts/Arial.ttf";

       $tmpFrom = 0;
       $black = imagecolorallocate($imageBar, 0, 0, 0);
       for ($i=1;$i<=$NbYears;$i++)
           { if ($A_Bar[$i] != 0) imagefilledrectangle($imageBar,$tmpFrom,$BarHeight-$FontHeight,$tmpFrom+20,$BarHeight-$FontHeight-$A_Bar[$i],$A_PieColor[$i]);
             imagettftext($imageBar,8,90,$tmpFrom+15,$BarHeight-2,$black,$FontPath,$A_Years[$i]);
             $tmpFrom += 30;
           }

       if ($opt_Pie3D)
          { $tmpFrom = 0;
            for ($i=1;$i<=$NbYears;$i++)
                { if ($A_Bar[$i] != 0) 
                     { $values = array($tmpFrom+20,$BarHeight-$FontHeight,
                                       $tmpFrom+25,$BarHeight-$FontHeight-5,
                                       $tmpFrom+25,$BarHeight-$FontHeight-$A_Bar[$i]-5, //+5
                                       $tmpFrom+20,$BarHeight-$FontHeight-$A_Bar[$i]);
                       imagefilledpolygon($imageBar,$values,4,$A_PieColorDarken[$i]);
                       
                       $values = array($tmpFrom,$BarHeight-$FontHeight-$A_Bar[$i],
                                       $tmpFrom+5,$BarHeight-$FontHeight-$A_Bar[$i]-5,
                                       $tmpFrom+25,$BarHeight-$FontHeight-$A_Bar[$i]-5,
                                       $tmpFrom+20,$BarHeight-$FontHeight-$A_Bar[$i]);
                       imagefilledpolygon($imageBar,$values,4,$A_PieColorDarken[$i]);
                     }
                  $tmpFrom += 30;
                }
          }
          
       $tmpMedia = $tmpTotalY/($n-1);
       $tmpMediaPerc = $tmpMedia * ($BarHeight-$FontHeight-10) / $tmpMax;
       $gray = imagecolorallocate($imageBar,0x80,0x80,0x80);
       $style = Array(
                $gray, 
                $gray, 
                $gray, 
                $gray, 
                IMG_COLOR_TRANSPARENT, 
                IMG_COLOR_TRANSPARENT, 
                IMG_COLOR_TRANSPARENT, 
                IMG_COLOR_TRANSPARENT);
       imagesetstyle($imageBar, $style);
       imageline($imageBar,0,$BarHeight-$FontHeight-$tmpMediaPerc,$BarWidth-1,$BarHeight-$FontHeight-$tmpMediaPerc,IMG_COLOR_STYLED);

       imageline($imageBar,$BarWidth-20,$BarHeight-$FontHeight,$BarWidth-20,5,$black);
       if ($parType == "ORDERS")
          imagettftext($imageBar,8,90,$BarWidth-8,$BarHeight-$FontHeight-$tmpMediaPerc-2,$gray,$FontPath,sprintf('%0.2f', $tmpMedia));
       else
          imagettftext($imageBar,8,90,$BarWidth-8,$BarHeight-$FontHeight-$tmpMediaPerc-2,$gray,$FontPath,sprintf('%0.2f', $tmpMedia).$tmpCur);
       
       imageline($imageBar,$BarWidth-1,$BarHeight-$FontHeight,0,$BarHeight-$FontHeight,$black);
       ob_start();
       imagepng($imageBar);
       $img = ob_get_clean();
       echo "<img src='data:image/gif;base64," . base64_encode($img) . "'>";
       imagedestroy($imageBar);
       echo '<br><em><font color="#808080">(' . esc_html__('Media line shown in gray','next-wc-orders') . ')</font></em>';
       echo "</td>";
     }
  else
     { $A_PiePerc[1] = $tmpNbStatusCompleted * 100 / $tmpTotalOrders;
       $A_PiePerc[2] = $tmpNbStatusOnhold * 100 / $tmpTotalOrders;
       $A_PiePerc[3] = $tmpNbStatusProcessing * 100 / $tmpTotalOrders;
       $A_PiePerc[4] = $tmpNbStatusPending * 100 / $tmpTotalOrders;
       $A_PiePerc[5] = $tmpNbStatusRefunded * 100 / $tmpTotalOrders;
       $A_PiePerc[6] = $tmpNbStatusCancelled * 100 / $tmpTotalOrders;
       $A_PiePerc[7] = $tmpNbStatusFailed * 100 / $tmpTotalOrders;
              
       $CenterX = $PieWidth/2;$CenterY = $PieHeight/2;
       if ($opt_Pie3D) $CenterY = $PieHeight/2-15;
       $ArcWidth = 200;$ArcHeight = 200;

       if ($opt_Pie3D)
          { $ArcHeight = $ArcHeight / 2;
            $Pie3DBorder = 30;
            $tmpFrom = 0; $tmpTo = 0;
            for ($c=$CenterY + $Pie3DBorder;$c>$CenterY;$c--)
                { for ($i=1;$i<=$NB_STATUS;$i++)
                      { if ($A_PiePerc[$i] != 0)
                           { $tmpNewTo = $tmpTo + $A_PiePerc[$i]*3.6;
                             $tmpFrom = $tmpTo;
                             $tmpTo = $tmpNewTo;
                             imagefilledarc($imagePie,$CenterX,$c,$ArcWidth,$ArcHeight,$tmpFrom,$tmpTo,$A_PieColorDarken[$i],IMG_ARC_PIE);
                           }
                      }
                }
          }

       $tmpFrom = 0; $tmpTo = 0;
       for ($i=1;$i<=$NB_STATUS;$i++)
           { if ($A_PiePerc[$i] != 0)
                { $tmpNewTo = $tmpTo + $A_PiePerc[$i]*3.6;
                  $tmpFrom = $tmpTo;
                  $tmpTo = $tmpNewTo;
                  imagefilledarc($imagePie,$CenterX,$CenterY,$ArcWidth,$ArcHeight,$tmpFrom,$tmpTo,$A_PieColor[$i],IMG_ARC_PIE);
                }
           }

       echo '<td align="center" valign="top">';
       ob_start();
       imagepng($imagePie);
       $img = ob_get_clean();
       echo "<img src='data:image/gif;base64," . base64_encode($img) . "'><br>";
       imagedestroy($imagePie);
       echo "</td></tr>"; 

       echo '<tr><td colspan="3">&nbsp;</td></tr>';
       
       echo '<tr align="left" valign="top">';
       echo '<th scope="row">' . esc_html__('Orders per month','next-wc-orders') . '</th>';
       echo '<td align="left" valign="top">';     

       $tmpSection = sanitize_text_field($_GET['section']);
       $tmpOptPie = 'optPie'.$tmpSection;
       ${'opt_Pie'.$tmpSection} = get_option($tmpOptPie);
       $tmpYearColor = "#c0c0c0"; if (${'opt_Pie'.$tmpSection} != "") $tmpYearColor = ${'opt_Pie'.$tmpSection};
       list($red,$green,$blue) = sscanf($tmpYearColor,"#%02x%02x%02x");
       $tmpChartColor = imageColorAllocate($imageBar,$red,$green,$blue);
       $tmpColorDarker = nwco_DarkenColor($tmpYearColor);
       list($red,$green,$blue) = sscanf($tmpColorDarker,"#%02x%02x%02x");
       $tmpChartColorDarken = imageColorAllocate($imageBar,$red,$green,$blue);
          
       $tmpMax = 0;
       $FontHeight = 30;
       for ($m=1;$m<=12;$m++)
           { if ($A_MonthOrders[$m] > $tmpMax) $tmpMax = $A_MonthOrders[$m];
             $A_Bar[$m] = 0;
           }
       for ($c=1;$c<=12;$c++)
           { if ($A_MonthOrders[$c] != 0) $A_Bar[$c] = $A_MonthOrders[$c] * ($BarHeight-$FontHeight-10) / $tmpMax;
           }
                   
       $upload_path = wp_upload_dir();     
       $path = $upload_path['basedir'];
       $FontPath = plugin_dir_path( __DIR__ ) . "fonts/Arial.ttf";
       
       $A_MonthName[1] = esc_html__('Jan','next-wc-orders');
       $A_MonthName[2] = esc_html__('Feb','next-wc-orders');
       $A_MonthName[3] = esc_html__('Mar','next-wc-orders');
       $A_MonthName[4] = esc_html__('Apr','next-wc-orders');
       $A_MonthName[5] = esc_html__('May','next-wc-orders');
       $A_MonthName[6] = esc_html__('Jun','next-wc-orders');
       $A_MonthName[7] = esc_html__('Jul','next-wc-orders');
       $A_MonthName[8] = esc_html__('Aug','next-wc-orders');
       $A_MonthName[9] = esc_html__('Sep','next-wc-orders');
       $A_MonthName[10] = esc_html__('Oct','next-wc-orders');
       $A_MonthName[11] = esc_html__('Nov','next-wc-orders');
       $A_MonthName[12] = esc_html__('Dec','next-wc-orders');
       $tmpFrom = 0;
       $black = imagecolorallocate($imageBar, 0, 0, 0);
       for ($i=1;$i<=12;$i++)
           { if ($A_Bar[$i] != 0) imagefilledrectangle($imageBar,$tmpFrom,$BarHeight-$FontHeight,$tmpFrom+20,$BarHeight-$FontHeight-$A_Bar[$i],$tmpChartColor);
             imagettftext($imageBar,8,90,$tmpFrom+15,$BarHeight-2,$black,$FontPath,$A_MonthName[$i]);
             $tmpFrom += 30;
           }

       if ($opt_Pie3D)
          { $tmpFrom = 0;
            for ($i=1;$i<=12;$i++)
                { if ($A_Bar[$i] != 0) 
                     { $values = array($tmpFrom+20,$BarHeight-$FontHeight,
                                       $tmpFrom+25,$BarHeight-$FontHeight-5,
                                       $tmpFrom+25,$BarHeight-$FontHeight-$A_Bar[$i]-5, //+5
                                       $tmpFrom+20,$BarHeight-$FontHeight-$A_Bar[$i]);
                       imagefilledpolygon($imageBar,$values,4,$tmpChartColorDarken);
                       
                       $values = array($tmpFrom,$BarHeight-$FontHeight-$A_Bar[$i],
                                       $tmpFrom+5,$BarHeight-$FontHeight-$A_Bar[$i]-5,
                                       $tmpFrom+25,$BarHeight-$FontHeight-$A_Bar[$i]-5,
                                       $tmpFrom+20,$BarHeight-$FontHeight-$A_Bar[$i]);
                       imagefilledpolygon($imageBar,$values,4,$tmpChartColorDarken);
                     }
                  $tmpFrom += 30;
                }
          }
           
       $tmpMedia = $tmpTotalOrders/12;
       $tmpMediaPerc = $tmpMedia * ($BarHeight-$FontHeight-10) / $tmpMax;
       $gray = imagecolorallocate($imageBar,0x80,0x80,0x80);
       $style = Array(
                $gray, 
                $gray, 
                $gray, 
                $gray, 
                IMG_COLOR_TRANSPARENT, 
                IMG_COLOR_TRANSPARENT, 
                IMG_COLOR_TRANSPARENT, 
                IMG_COLOR_TRANSPARENT);
       imagesetstyle($imageBar, $style);
       imageline($imageBar,0,$BarHeight-$FontHeight-$tmpMediaPerc,$BarWidth-1,$BarHeight-$FontHeight-$tmpMediaPerc,IMG_COLOR_STYLED);
       
       imageline($imageBar,$BarWidth-20,$BarHeight-$FontHeight,$BarWidth-20,5,$black);
       imagettftext($imageBar,8,90,$BarWidth-8,$BarHeight-$FontHeight-$tmpMediaPerc-2,$gray,$FontPath,sprintf('%0.2f', $tmpMedia));
       
       imageline($imageBar,$BarWidth-1,$BarHeight-$FontHeight,0,$BarHeight-$FontHeight,$black);

       ob_start();
       imagepng($imageBar);
       $img = ob_get_clean();
       echo "<img src='data:image/gif;base64," . base64_encode($img) . "'>";
       imagedestroy($imageBar);
       echo '<br><em><font color="#808080">(' . esc_html__('Media line shown in gray','next-wc-orders') . ')</font></em>';
       
       echo "</td><td>";
       echo "<table cellpadding=2 cellspacing=0 border=1>";
       echo '<tr><td align=center><b>' . esc_html__('Month','next-wc-orders') . '</b></td><td><b>' . esc_html__('Total orders','next-wc-orders') . '</b></td></tr>';
       for ($m=1;$m<=12;$m++)
           { echo '<tr><td bgcolor="' . esc_attr($tmpYearColor) . '"><b>' . esc_attr($A_MonthName[$m]) . '</b></td><td align=right>'. esc_attr($A_MonthOrders[$m]) . '</td></tr>';
           }
       echo "<tr><td align=center><b>" . esc_html__('TOTAL','next-wc-orders') . "</b></td><td align=right><b>" . esc_attr($tmpTotalOrders) . "</b></td></tr>";
       echo '</table>';
       echo "</td></tr>";
       
       echo '<tr><td colspan="3">&nbsp;</td></tr>';

       echo '<tr align="left" valign="top">';
       echo '<th scope="row">' . esc_html__('Total sales per month','next-wc-orders') . '</th>';
       echo '<td>';      
       
       $imageBar2 = imagecreatetruecolor($BarWidth,$BarHeight);
       $transparent_color = imagecolorallocate($imageBar2, 0xff, 0xff, 0xff);
       imagefill($imageBar2,0,0,$transparent_color);
       imagecolortransparent($imageBar2,$transparent_color);
       list($red,$green,$blue) = sscanf($tmpYearColor,"#%02x%02x%02x");
       $tmpChartColor = imageColorAllocate($imageBar2,$red,$green,$blue);
       $tmpColorDarker = nwco_DarkenColor($tmpYearColor);
       list($red,$green,$blue) = sscanf($tmpColorDarker,"#%02x%02x%02x");
       $tmpChartColorDarken = imageColorAllocate($imageBar2,$red,$green,$blue);
          
       $tmpMax = 0;
       $FontHeight = 30;
       for ($m=1;$m<=12;$m++)
           { if ($A_MonthSales[$m] > $tmpMax) $tmpMax = $A_MonthSales[$m];
             $A_Bar[$m] = 0;
           }
       for ($c=1;$c<=12;$c++)
           { if ($A_MonthSales[$c] != 0) $A_Bar[$c] = $A_MonthSales[$c] * ($BarHeight-$FontHeight-10) / $tmpMax;
           }
                   
       $upload_path = wp_upload_dir();     
       $path = $upload_path['basedir'];
       $FontPath = plugin_dir_path( __DIR__ ) . "fonts/Arial.ttf";
       
       $A_MonthName[1] = esc_html__('Jan','next-wc-orders');
       $A_MonthName[2] = esc_html__('Feb','next-wc-orders');
       $A_MonthName[3] = esc_html__('Mar','next-wc-orders');
       $A_MonthName[4] = esc_html__('Apr','next-wc-orders');
       $A_MonthName[5] = esc_html__('May','next-wc-orders');
       $A_MonthName[6] = esc_html__('Jun','next-wc-orders');
       $A_MonthName[7] = esc_html__('Jul','next-wc-orders');
       $A_MonthName[8] = esc_html__('Aug','next-wc-orders');
       $A_MonthName[9] = esc_html__('Sep','next-wc-orders');
       $A_MonthName[10] = esc_html__('Oct','next-wc-orders');
       $A_MonthName[11] = esc_html__('Nov','next-wc-orders');
       $A_MonthName[12] = esc_html__('Dec','next-wc-orders');
       $tmpFrom = 0;
       $black = imagecolorallocate($imageBar, 0, 0, 0);
       for ($i=1;$i<=12;$i++)
           { if ($A_Bar[$i] != 0) imagefilledrectangle($imageBar2,$tmpFrom,$BarHeight-$FontHeight,$tmpFrom+20,$BarHeight-$FontHeight-$A_Bar[$i],$tmpChartColor);
             imagettftext($imageBar2,8,90,$tmpFrom+15,$BarHeight-2,$black,$FontPath,$A_MonthName[$i]);
             $tmpFrom += 30;
           }

       if ($opt_Pie3D)
          { $tmpFrom = 0;
            for ($i=1;$i<=12;$i++)
                { if ($A_Bar[$i] != 0) 
                     { $values = array($tmpFrom+20,$BarHeight-$FontHeight,
                                       $tmpFrom+25,$BarHeight-$FontHeight-5,
                                       $tmpFrom+25,$BarHeight-$FontHeight-$A_Bar[$i]-5, //+5
                                       $tmpFrom+20,$BarHeight-$FontHeight-$A_Bar[$i]);
                       imagefilledpolygon($imageBar2,$values,4,$tmpChartColorDarken);
                       
                       $values = array($tmpFrom,$BarHeight-$FontHeight-$A_Bar[$i],
                                       $tmpFrom+5,$BarHeight-$FontHeight-$A_Bar[$i]-5,
                                       $tmpFrom+25,$BarHeight-$FontHeight-$A_Bar[$i]-5,
                                       $tmpFrom+20,$BarHeight-$FontHeight-$A_Bar[$i]);
                       imagefilledpolygon($imageBar2,$values,4,$tmpChartColorDarken);
                     }
                  $tmpFrom += 30;
                }
          }
           
       $tmpMedia = $tmpTotalSales/12;
       $tmpMediaPerc = $tmpMedia * ($BarHeight-$FontHeight-10) / $tmpMax;
       $gray = imagecolorallocate($imageBar2,0x80,0x80,0x80);
       $style = Array(
                $gray, 
                $gray, 
                $gray, 
                $gray, 
                IMG_COLOR_TRANSPARENT, 
                IMG_COLOR_TRANSPARENT, 
                IMG_COLOR_TRANSPARENT, 
                IMG_COLOR_TRANSPARENT);
       imagesetstyle($imageBar2, $style);
       imageline($imageBar2,0,$BarHeight-$FontHeight-$tmpMediaPerc,$BarWidth-1,$BarHeight-$FontHeight-$tmpMediaPerc,IMG_COLOR_STYLED);
       
       imageline($imageBar2,$BarWidth-20,$BarHeight-$FontHeight,$BarWidth-20,5,$black);
       imagettftext($imageBar2,8,90,$BarWidth-8,$BarHeight-$FontHeight-$tmpMediaPerc-2,$gray,$FontPath,sprintf('%0.2f', $tmpMedia).$tmpCur);
       
       imageline($imageBar2,$BarWidth-1,$BarHeight-$FontHeight,0,$BarHeight-$FontHeight,$black);

       ob_start();
       imagepng($imageBar2);
       $img = ob_get_clean();
       echo "<img src='data:image/gif;base64," . base64_encode($img) . "'>";
       imagedestroy($imageBar2);
       echo '<br><em><font color="#808080">(' . esc_html__('Media line shown in gray','next-wc-orders') . ')</font></em>';
       
       echo "</td><td>";
       echo "<table cellpadding=2 cellspacing=0 border=1>";
       echo '<tr><td align=center><b>' . esc_html__('Month','next-wc-orders') . '</b></td><td><b>' . esc_html__('Total sales','next-wc-orders') . ' (' . esc_attr($tmpCur) . ')</b></td></tr>';
       for ($m=1;$m<=12;$m++)
           { echo '<tr><td bgcolor="' . esc_attr($tmpYearColor) . '"><b>' . esc_attr($A_MonthName[$m]) . '</b></td><td align=right>'. sprintf('%0.2f', esc_attr($A_MonthSales[$m])) . esc_attr($tmpCur) . '</td></tr>';
           }
       echo "<tr><td align=center><b>" . esc_html__('TOTAL','next-wc-orders') . "</b></td><td align=right><b>" . esc_attr($tmpTotalSales) . esc_attr($tmpCur) . "</b></td></tr>";
       echo '</table>';
       echo "</td>";
     }
}

function nwco_get_parent_terms(int $par_product_id)
{ $product_terms = get_the_terms($par_product_id, 'product_cat');

  if ($product_terms)
     { foreach ($product_terms as $product_term)
               { while ($product_term->parent>0)
                       { $product_term = get_term_by("id", $product_term->parent, "product_cat");
                       }

                 $parent_terms[] = (object)[
                 'term_id' => $product_term->term_id,
                 'name' => $product_term->name,
                 'link' => get_term_link($product_term->term_id, 'product_cat')];
               }
       return $parent_terms;
     }
  else
    { return false;
    }
}

function nwco_StatsByCat($parYear)
{ $gCur = get_woocommerce_currency_symbol();
  $opt_Pie3D = get_option('optPie3D');
  
  $orders = wc_get_orders( array('numberposts' => -1) );

  $tmpUncategorized=0;
  $strCatList = ""; $tmpIndCatList = 0;
  $A_CatList[0] = esc_html__('Uncategorized','next-wc-orders'); $A_NbCat[0]=0;
  foreach($orders as $order)
         { $order_id = $order->get_id();
           $order = wc_get_order($order_id);

          $order_data = $order->get_data();
          $year_created = $order_data['date_created']->date('Y');

          if ($parYear == $year_created)
             { $order_id = $order_data['id'];
               $order_parent_id = $order_data['parent_id'];

               if ($order_data['total'] >= $parTotal)
                  { $order_total = $order_data['total'];
                    
                   $order = wc_get_order($order_id);
                   foreach ($order->get_items() as $item_key => $item_values)
                           { $item_name = $item_values->get_name();
                             $item_type = $item_values->get_type();
  
                             $product_id = $item_values->get_product_id();
                             $wc_product = $item_values->get_product();
                             
                             $item_data = $item_values->get_data();
                             $product_name = $item_data['name'];
                             $product_id = $item_data['product_id'];
                             $variation_id = $item_data['variation_id'];
                             $quantity = $item_data['quantity'];
                             $tax_class = $item_data['tax_class'];
                             $line_subtotal = $item_data['subtotal'];
                             $line_subtotal_tax = $item_data['subtotal_tax'];
                             $line_total = $item_data['total'];
                             $line_total_tax = $item_data['total_tax'];

                             $product_price = $line_total / $quantity;
      
                             $terms = wp_get_post_terms($product_id,'product_cat',array('fields' => 'all'));
                             if ($terms)
                                { foreach($terms as $term) 
                                         { $tmpPos = strpos($strCatList,$term->name);
                                           if ($tmpPos === false)
                                              { $strCatList .= "|" . $term->name;
                                                $tmpIndCatList++;
                                                $A_NbCat[$tmpIndCatList] = 1;
                                                $A_CatList[$tmpIndCatList] = $term->name;

                                                $parentcats = get_ancestors($term->term_id,'product_cat');
                                                $NbParents = count($parentcats);
                                                $TopCat = "";
                                                foreach($parentcats as $parentcat)
                                                       { $TopCat = $parentcat;
                                                       }
                                               
                                                if($term = get_term_by('id',$TopCat,'product_cat'))
                                                  { if ($TopCat) $A_TopLevelCat[$tmpIndCatList] = $term->name;
                                                  }
                                              }
                                           else
                                              { for ($c=0;$c<=$tmpIndCatList;$c++)
                                                    { if ($A_CatList[$c] == $term->name)
                                                         { $A_NbCat[$c]++;
                                                           break;
                                                         }
                                                    }
                                              }
                                         }
                                }
                             else  
                                { $tmpUncategorized++;
                                }
                           }
                  }
             }
         }
  echo "<br>";
  $A_NbCat[0] = $tmpUncategorized;

  $BarHeight = ($tmpIndCatList+1) * 30 ;$BarWidth = 380;
  $imageBar = imagecreatetruecolor($BarWidth,$BarHeight);
  $transparent_color = imagecolorallocate($imageBar, 0xff, 0xff, 0xff);
  imagefill($imageBar,0,0,$transparent_color);
  imagecolortransparent($imageBar,$transparent_color);

  $tmpSection = sanitize_text_field($_GET['year']);
  $tmpOptPie = 'optPie'.$tmpSection;
  ${'opt_Pie'.$tmpSection} = get_option($tmpOptPie);
  $tmpYearColor = "#c0c0c0"; if (${'opt_Pie'.$tmpSection} != "") $tmpYearColor = ${'opt_Pie'.$tmpSection};
  list($red,$green,$blue) = sscanf($tmpYearColor,"#%02x%02x%02x");
  $tmpChartColor = imageColorAllocate($imageBar,$red,$green,$blue);
  $tmpColorDarker = nwco_DarkenColor($tmpYearColor);
  list($red,$green,$blue) = sscanf($tmpColorDarker,"#%02x%02x%02x");
  $tmpChartColorDarken = imageColorAllocate($imageBar,$red,$green,$blue);
    
  $upload_path = wp_upload_dir();     
  $path = $upload_path['basedir'];
  $FontPath = plugin_dir_path( __DIR__ ) . "fonts/Arial.ttf";
          
  $black = imagecolorallocate($imageBar, 0, 0, 0);
  $tmpMax = 0;
  for ($c=0;$c<=$tmpIndCatList;$c++)
           { if ($A_NbCat[$c] > $tmpMax) $tmpMax = $A_NbCat[$c];
             $A_BarPerc[$c] = 0;
           }

  $tmpFrom = 5;
  $FontHeight = 0;
  $tmpStartX = 35;

  for ($c=0;$c<=$tmpIndCatList;$c++)
      { if ($A_NbCat[$c] != 0) $A_BarPerc[$c] = $A_NbCat[$c] * ($BarWidth-$tmpStartX-10) / $tmpMax;
      }
  
  for ($c=0;$c<=$tmpIndCatList;$c++)
      { if ($A_BarPerc[$c] > 0) imagefilledrectangle($imageBar,$tmpStartX,$tmpFrom,$tmpStartX+$A_BarPerc[$c],$tmpFrom+20,$tmpChartColor);
        $tmpParentCat = ($A_TopLevelCat[$c]==""?"":" [" . $A_TopLevelCat[$c] . "]");
        imagettftext($imageBar,9,0,$tmpStartX+5,$tmpFrom+15,$black,$FontPath,$A_CatList[$c] . $tmpParentCat);
        imagettftext($imageBar,9,0,5,$tmpFrom+15,$gray,$FontPath,$A_NbCat[$c]); 
        $tmpFrom += 30;
      }
 
  if ($opt_Pie3D)
     { $tmpFrom = 0;
       for ($c=0;$c<=$tmpIndCatList;$c++)
           { if ($A_BarPerc[$c] != 0) 
                { $values = array($tmpStartX+5,$tmpFrom,
                                  $tmpStartX,$tmpFrom+5,
                                  $tmpStartX+$A_BarPerc[$c]-$FontHeight,$tmpFrom+5,
                                  $tmpStartX+$A_BarPerc[$c]-$FontHeight+5,$tmpFrom);
                  imagefilledpolygon($imageBar,$values,4,$tmpChartColorDarken);
                       
                  $values = array($tmpStartX+$A_BarPerc[$c]-$FontHeight+5,$tmpFrom,
                                  $tmpStartX+$A_BarPerc[$c]-$FontHeight+5,$tmpFrom+20,
                                  $tmpStartX+$A_BarPerc[$c]-$FontHeight,$tmpFrom+25,
                                  $tmpStartX+$A_BarPerc[$c]-$FontHeight,$tmpFrom+5);
                  imagefilledpolygon($imageBar,$values,4,$tmpChartColorDarken);
                }
             $tmpFrom += 30;
           }
     }
           
  imageline($imageBar,$tmpStartX,0,$tmpStartX,$BarHeight,$black);
  
  $gray = imagecolorallocate($imageBar,0x80,0x80,0x80);
  $style = Array($gray, 
                 $gray, 
                 $gray, 
                 $gray, 
                 IMG_COLOR_TRANSPARENT, 
                 IMG_COLOR_TRANSPARENT, 
                 IMG_COLOR_TRANSPARENT, 
                 IMG_COLOR_TRANSPARENT);
  imagesetstyle($imageBar, $style);
  $A_BarPercMax = $tmpMax * ($BarWidth-10) / $tmpMax;
  imageline($imageBar,$A_BarPercMax,0,$A_BarPercMax,$BarHeight,IMG_COLOR_STYLED);
  imagettftext($imageBar,8,90,$A_BarPercMax-5,20,$gray,$FontPath,$tmpMax);

  ob_start();
  imagepng($imageBar);
  $img = ob_get_clean();
  echo "<img src='data:image/gif;base64," . base64_encode($img) . "'>";
  imagedestroy($imageBar);
  echo '<br><em><font color="#808080">' . esc_html__('Number of products per category with [top-level category].','next-wc-orders') . '<br>' . esc_html__('Products with several categories will be displayed as many times.','next-wc-orders') . '</font></em>';
}
                      
function nwco_DarkenColor($parRGB,$ParDark=2)
{ $diese = (strpos($parRGB,'#') !== false)?'#':'';
  $parRGB = (strlen($parRGB) == 7)?str_replace('#','',$parRGB):((strlen($parRGB)==6)?$parRGB:false);
  if (strlen($parRGB) != 6) return $diese . '000000';
  $ParDark = ($ParDark > 1)?$ParDark:1;
 
  list($red,$green,$blue) = str_split($parRGB,2);
  $red = sprintf("%02X",floor(hexdec($red)/($ParDark/1)));
  $green= sprintf("%02X",floor(hexdec($green)/($ParDark/1)));
  $blue = sprintf("%02X",floor(hexdec($blue)/($ParDark/1)));

  return $diese . $red . $green . $blue;
}