
<div class="wrap">
	<form name="promotions-manage" id="promotions-manage" method="post" action="<?php $this->friendly_page_link( 'promotions' ); ?>">
		<h2>Manage Promotions (<a href="#addproject">add new</a>)</h2>
		<div class="tablenav">
			<div class="alignleft">
				<input name="delete_promotions" id="delete_promotions" class="button-secondary delete" type="submit" value="Delete" />
			</div>
			<br class="clear" />
		</div>
		<br class="clear" />
		
		<table class="widefat"> <!-- Start Manage Table -->
			<thead>
				<tr>
					<th class="check-column" scope="col"><input id="selectall" name="selectall" type="checkbox" /></th>
					<th scope="col">Promotions</th>
					<th scope="col">Date End</th>
					<th scope="col">Description</th>
				</tr>
			</thead>
			<tbody>
				<?php $this->promotion_rows(); ?>
			</tbody>
		</table> <!-- End Manage Table -->
		
	</form> <!-- End the manage form -->
	<div class="tablenav">
		<br class="clear" />
	</div>
	<br class="clear" />
</div>

 <!-- End Wrap -->