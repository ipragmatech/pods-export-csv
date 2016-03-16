<?php
/**
 * Plugin Name: Pods Export CSV
 * Plugin URI: 
 * Description: Export 
 * Author: iPragmatech Solutions
 * Author URI: http://ipragmatech.com
 * Version: 0.9.1
 * License: GPLv3
*/

/** Copyright 2014 iPragmatech Solutions Pvt. Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License, version 3, as
  published by the Free Software Foundation.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

if (!class_exists('Pods_Export_Bulk_Actions')) {

	class Pods_Export_Bulk_Actions {
		public $delimiter;
		public $type;
		
		public function __construct() {
			if(is_admin()) {
				// admin actions/filters
				add_filter( 'pods_ui_pre_init', array(&$this, 'pods_ui_add_bulk_actions') , 10, 3 );
				add_action( 'admin_enqueue_scripts', array(&$this, 'pods_ui_admin_enqueue_scripts'));
			}
			
			$this->delimiter = ',';
			$this->type = 'sv';
		}
		
		public function pods_ui_admin_enqueue_scripts(){
			if( stripos(get_current_screen()->id,'pods-manage') ) {
				wp_register_style( 'pods-export-csv', plugin_dir_url( __FILE__ ) . 'css/pods-export-csv.css', false, '1.0.0' );
			}
		}
		
		/**
		 * add the custom Bulk Action to the select menus
		 */
		public function pods_ui_add_bulk_actions( $args ) {
			//error_log(print_r($args, true));
			$args['actions_bulk']['export_pods_as_csv'] =  array (
					'label' => 'Export  AS Csv',
					'callback' => array (
								&$this,
								'export_pods_as_csv' 
						) 
					
			);
			return $args;
		}	
		
		/**
		 * Callback Method to export as CSV
		 */
		public function export_pods_as_csv($obj) {
			$page = explode("-",$_GET['page']);
			$this->pod = pods($page[2]);
			
			if (isset($_POST['export_company'])) {
				$export_fields = empty ( $_POST ['export_fields'] ) ? array () : $_POST ['export_fields'];
				error_log(print_r($export_fields, true));
				
				foreach ( $this->pod->fields() as $field ) {
					if (in_array ( $field ['id'], $export_fields )) {
						$columns [$field ['name']] = $field ['label'];
					}
				}
				
				$ids = implode ( ",", $_GET ['action_bulk_ids'] );
				
				$params = array(
						'where' => "id IN ($ids)",
						'orderby' => "id desc",
						'limit' => -1
				);
					
				$this->pod = pods ( 'small_company',$params );
				
				$data = array (
						'columns' => $columns,
						'items' => $this->pod->data (),
						'fields' => $this->pod->fields()
				);
				
				error_log(print_r($this->type, true));
				error_log(print_r($this->delimiter, true));
					
				$migrate = pods_migrate ( $this->type, $this->delimiter, $data );
					
				$migrate->export ();
					
				$export_file = $migrate->save ();
				error_log(print_r($export_file, true));
			}
			
			$this->fields_form();
		}
		
		/**
		 * Select the pods fields to be exported in csv
		 */
		public function fields_form() {
			?>
			<div class="wrap pods-admin pods-ui">
				<h2>Choose Export Fields</h2>
				<?php if ( !empty( $export_file ) ){ ?>
					<div id="message" class="updated notice">
						<p>
							<strong>Success:</strong> Your export is ready, you can download it 
							<a href="<?php echo $export_file;?>" target="_blank">here</a>
						</p>
					</div>
								
				<?php }?>
				<form method="POST" id="export_form" class="ac-custom ac-checkbox ac-cross">
					<ul>
						<?php foreach (  $this->pod->fields() as $field_name => $detail ) { ?>
							<li class="av_one_fourth">
								<input type="checkbox" name="export_fields[]" id="export_fields_<?php echo $detail[ 'id' ]; ?>" value="<?php echo $detail[ 'id' ]; ?>" /> 
								<label for="cb"><?php echo $detail[ 'label' ];?> </label>
							</li>
						<?php } ?>
					</ul>
					<input type="submit" id="export_company" value="Export" name="export_company" class="export_company_button">
				</form>
			</div>
			<?php
		}
	}
}

$Pods_Export_Bulk_Actions = new Pods_Export_Bulk_Actions ();
