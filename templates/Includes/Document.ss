<% if isHidden != true %>
<div class="document $Extension">
	<% if Title %>
		<h4><a href="$Link" title="Download $Title">$Title</a></h4>
	<% else %>
		<h4><a href="$Link" title="Download $FilenameWithoutID">$FilenameWithoutID</a></h4>
	<% end_if %>
	
	<p class='details'>
		<strong>$FilenameWithoutID</strong>
		| $Extension
		| $FileSizeFormatted
		| Last Changed: $LastChanged.Nice
	</p>
	<% if Description %>
		<p>$DescriptionWithLineBreak</p>
	<% end_if %>
</div>
<% end_if %>