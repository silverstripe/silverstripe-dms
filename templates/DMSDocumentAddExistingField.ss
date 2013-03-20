<div id="document" class="ss-add field treedropdown searchable">
	<div class="document-add-existing <% if useFieldContext %>field<% else %>link-editor-context<% end_if %>">
	<% if useFieldContext %>
		<h3>
	<% else %>
		<div>
	<% end_if %>
			<span class="step-label">
				<% if useFieldContext %>
					<span class="flyout">1</span><span class="arrow"></span>
					<strong class="title">Link a Document</strong>
				<% else %>
				<% end_if %>
			</span>
	<% if useFieldContext %>
		</h3>
	<% else %>
		</div>
	<% end_if %>

	<% if useFieldContext %>
	<% else %>
		<label>Link a Document</label>
		<div class="middleColumn">
	<% end_if %>
		
			<input class="document-autocomplete text" type="text" placeholder="Search by ID or filename" />
			<!-- <span>or Add from page</span> -->
			$fieldByName(PageSelector)

		<div class="document-list"></div>

	<% if useFieldContext %>
	<% else %>
		</div>
	<% end_if %>

	</div>

	<div class="ss-assetuploadfield <% if useFieldContext %>field<% else %>link-editor-context<% end_if %>">
		<div class="step4">
			<span class="step-label">
				<% if useFieldContext %>
					<span class="flyout">2</span><span class="arrow"></span>
					<strong class="title">Edit Document Details</strong>
				<% else %>
					<label>Selected Document</label>
				<% end_if %>
			</span>
		</div>
		<!-- <div class="fileOverview"></div> -->

		<% if useFieldContext %>
		<% else %>
			<div class="middleColumn">
		<% end_if %>
			<ul class="files ss-uploadfield-files ss-add-files"></ul>
		<% if useFieldContext %>
		<% else %>
			</div>
		<% end_if %>

	</div>
</div>