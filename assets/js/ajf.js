jQuery(document).ready(function ($) {
	$('pre.acf-json-field-output').each(function () {
		const $pre = $(this);

		const pre_lines = $pre.html().split(/\n/);
		const computed_tab_size = parseInt(window.getComputedStyle(this).getPropertyValue('tab-size'), 10) || 2;

		$pre.empty();

		pre_lines.forEach((line) => {
			const $line_container = $('<div class="acf-json-field-output-line"></div>');

			const leading_whitespace_match = line.match(/^(\s*)/);
			const leading_whitespace = leading_whitespace_match ? leading_whitespace_match[1] : '';
			let indent_length = 0;

			for (let whitespace of leading_whitespace) {
				if (whitespace === '\t') {
					indent_length += computed_tab_size;
				} else {
					indent_length += 1;
				}
			}

			$line_container.css({
				'text-indent': `-${indent_length}ch`,
				'padding-left': `${indent_length}ch`
			});

			$line_container.html(line);
			$pre.append($line_container);
		});
	});
});
