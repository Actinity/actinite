(function () {
	'use strict';

	var global = tinymce.util.Tools.resolve('tinymce.PluginManager');

	/*var register$1 = function (editor) {
		editor.addCommand('InsertHorizontalRule', function () {
			editor.execCommand('mceInsertContent', false, '<hr />');
		});
	};*/

	var register = function (editor) {
		var onAction = function () {

			let selectedId = editor.selection.getNode();

			if(selectedId) {
				selectedId = selectedId.getAttribute('data-ac-asset') || null;
			}

			window.$mitt.emit('assets:open',{
				maxWidth: 1200,
				callback: (asset) => {

					if(asset) {
						//let width = 800;
						//let height = Math.round(i.height / i.width * width);

						editor.insertContent('<p><img' +
							' src="'+asset.url+'"' +
							' data-ac-asset="'+asset.id+'"' +
							//' width="'+width+'"' +
							//' height="'+height+'"' +
							'/></p>'
						);
					}


				},
				asset: selectedId,
				type: 'image'
			});

			//return editor.execCommand('InsertHorizontalRule');
		};
		editor.ui.registry.addButton('acimage', {
			icon: 'image',
			tooltip: 'Insert image',
			onAction: onAction
		});
		editor.ui.registry.addMenuItem('acimage', {
			icon: 'image',
			text: 'Insert image',
			onAction: onAction
		});
	};

	function Plugin () {
		global.add('acimage', function (editor) {
			register(editor);
		});
	}

	Plugin();

}());


