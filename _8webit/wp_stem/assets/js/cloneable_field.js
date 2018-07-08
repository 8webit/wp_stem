jQuery(function ($) {
    "use strict";

    var wp_stem_meta_field = (function () {

        var sMetaboxFields = ".meta-box-field";

        var sCloneableSingle = ".cloneable";
        var sCloneableGroup = '.cloneable-group';
        var sCloneableEntry = '.cloneable-entry';

        var sFieldGroup = ".field-group";
        var sLabelWrapper = '.label-wrapper';
        var sFieldWrapper = '.field-wrapper';
        var sActionWrapper = '.wp-metabox-field-actions';

        var sMetaBoxFieldActions = '.meta-field-action';

        var btnAddClone = $('.button-add-clone');
        var btnDeleteClone = $('.button-remove-clone');
        var btnSortable = $('<a/>', {
            class: 'button button-link button-sortable',
            href: 'javascript:void(0)'
        }).append(
            $('<span/>', {
                class: 'dashicons dashicons-move'
            })
        );

        function initSortable() {
            $(sCloneableSingle).each(function () {
                $(this).children('.field-group')
                    .first()
                    .addClass('clone-identity');

                $(this).find(sFieldGroup)
                    .prepend(btnSortable.clone(true, true));
            });

            $(sCloneableGroup).each(function () {
                $(this).children('.box')
                    .first()
                    .addClass('clone-identity');

                $(this).find('.box > .title')
                    .prepend(btnSortable.clone(true, true));
            });
        }

        function initMetaboxes() {
            $(sMetaboxFields).each(function () {
                var singleCloneable = $(this).find(sCloneableSingle);
                var groupCloneable = $(this).find(sCloneableGroup);

                if (groupCloneable.length > 0) {
                    var index = 1;
                    var title = groupCloneable.find('.title');

                    createMetaBox(title.text(), $(this), groupCloneable);

                    groupCloneable.find(sCloneableEntry).each(function () {
                        var entryTitle = title.text() + " #" + index;
                        createMetaBox(entryTitle, groupCloneable, $(this));
                        index++;
                    });

                    groupCloneable.children(sMetaBoxFieldActions)
                        .appendTo(groupCloneable);
                    title.remove();
                }

                if (singleCloneable.length > 0) {
                    title = singleCloneable.find('.title');
                    createMetaBox(title.text(), $(this), singleCloneable);
                    title.remove();
                }
            });


            $('.box').find('.toggle-cloneable').each(function () {
                toggleMetaBox($(this));
            });
        }

        function createMetaBox(title, appendTo, elements) {
            var box = $('<div/>', {
                class: "box"
            }).append(
                $('<button />', {
                    class: "toggle-cloneable button-link",
                    html: '<span class="toggle-indicator" aria-hidden="true"></span>',
                    href: 'javascript:void(0)'
                })
            );

            if (title === "") {
                box.append(
                    $('<h3/>', {
                        text: elements.find(sLabelWrapper).first().text(),
                        class: "title"
                    })
                );
            } else {
                box.append(
                    $('<h3/>', {
                        text: title,
                        class: "title"
                    })
                );
            }

            box.append(
                $('<div/>', {
                    class: "inside"
                })
            );

            box.appendTo(appendTo);
            elements.appendTo(box.find(".inside"));

            box.children('.title, .toggle-cloneable').on('click', function (event) {
                event.preventDefault();

                toggleMetaBox($(this));
            });

            if (!box.parents(sCloneableGroup).length) {
                box.sortable({
                    axis: "y",
                    items: '> .inside .cloneable .field-group,  > .inside > .cloneable-group > .box',
                    cursor: "move",
                    containment: "parent",
                    handle: '.button-sortable',
                    opacity: 0.8,
                    revert: 200,
                    delay: 50
                });
            }

            return box;
        }

        function toggleMetaBox(boxAction) {
            if (boxAction.hasClass('toggle-cloneable')) {
                boxAction.toggleClass('rotate');
            } else {
                boxAction.siblings('.toggle-cloneable').toggleClass('rotate');
            }

            boxAction.nextAll('.inside').toggle();
        }


        function cloneableActionEvents() {
            $(document).on("click", '.button-add-clone', function (event) {
                event.preventDefault();

                addClone($(this));
            });

            btnDeleteClone.on("click", function (event) {
                event.preventDefault();

                removeClone($(this));
            });
        }

        function addClone(btnAdd) {
            if (btnAdd.parents(sCloneableGroup).length) {
                cloneGroup(btnAdd.parents(sCloneableGroup));
            } else {
                var cloneIdentity = btnAdd.parents(sCloneableSingle)
                    .first()
                    .find('.clone-identity')
                    .first()
                    .clone(true, true);

                var insertBefore = btnAdd.parents(sCloneableSingle)
                    .children(sMetaBoxFieldActions)
                    .last();

                cloneIdentity.removeClass('clone-identity');
                cloneIdentity.find('input, textarea').prop('disabled',false);
                cloneIdentity.show();

                cloneSingle(cloneIdentity, insertBefore,true);
            }
        }

        function removeClone(btnDelete) {
            var parent;
            var isCloneIdentity;
            var isCloneableGroup = btnDelete.parents(sCloneableGroup).length > 0;

            if (isCloneableGroup) {
                parent = btnDelete.parents('.box').first();
                isCloneIdentity = parent.hasClass('clone-identity');
            } else {
                parent = btnDelete.parents(sFieldGroup).first();
                isCloneIdentity = parent.hasClass('clone-identity');
            }

            if (isCloneIdentity) {
                parent.fadeOut(200, function () {
                    $(this).find('input, textarea').prop('disabled', true);
                    $(this).hide()
                });
            } else {
                parent.fadeOut(200, function () {
                    $(this).remove()
                });
            }
        }

        function cloneSingle(cloneIdentity, insertBefore, incrementalLabel) {
            incrementalLabel = incrementalLabel !== undefined ? incrementalLabel : true;

            var cFieldGroup = cloneIdentity.clone(true, true);

            var cLabelWrapper = cFieldGroup.find(sLabelWrapper).first().clone(true, true);
            var cFieldWrapper = cFieldGroup.find(sFieldWrapper).first().clone(true, true);
            var cFieldActions = cFieldGroup.find(sMetaBoxFieldActions).first().clone(true, true);

            var labelText = cLabelWrapper.find("label").first().text();
            var field = cFieldWrapper.find("[name]").first();
            var name = field.attr("name");
            var id = field.attr("id");

            if (name !== undefined) {
                id = id ? id : name;
                name = incrementAttr("name", name);
                id = incrementAttr("id", id);

                if (incrementalLabel) {
                    labelText = incrementLabel(insertBefore.parents('.inside').first(), labelText);
                }

                cFieldWrapper.find("[name]")
                    .attr("name", name)
                    .attr("id", id)
                    .attr("value", "");

                cLabelWrapper.find("label")
                    .attr("for", id)
                    .text(labelText);

                cFieldActions.children()
                    .removeClass('hide');

                cFieldGroup.empty();

                if (!cloneIdentity.parents(sCloneableGroup).length) {
                    cFieldGroup.append(btnSortable.clone(true, true));
                }

                cFieldGroup.append(cLabelWrapper);
                cFieldGroup.append(cFieldWrapper);
                cFieldGroup.append(cFieldActions);
                cFieldGroup.insertBefore(insertBefore);

                if (field.hasClass("spectrum")) {
                    spectrum();
                    cFieldWrapper.find('.sp-replacer').last().remove();
                }

                if (cFieldWrapper.hasClass('stem-media-uploader')) {
                    cFieldGroup.find('.stem-remove-wp-media').hide();
                    cFieldGroup.find('.stem-upload-wp-media').removeClass('hidden');
                    cFieldGroup.find('.media-data').empty();
                }
            } else {
                console.error("can not find input name to clone!");
            }
        }

        function cloneGroup(pCloneableGroup) {
            var pMetaboxFields = pCloneableGroup.parents(sMetaboxFields);

            var index = 2;
            var baseBoxTitle = pMetaboxFields.find('.title').first().text();
            var boxTitle = baseBoxTitle + " #" + index;

            while (findText(pCloneableGroup.find('.title'), boxTitle)) {
                index++;
                boxTitle = baseBoxTitle + " #" + index;
            }

            // clone field from first inside single by single
            pCloneableGroup.find('.clone-identity')
                .first()
                .find(sFieldGroup)
                .each(function () {
                    cloneSingle($(this), pCloneableGroup.children(sMetaBoxFieldActions), false);
                });

            var boxOfClones = createMetaBox(boxTitle, pCloneableGroup, pCloneableGroup.children(sFieldGroup))
                .insertBefore(pCloneableGroup.children(sMetaBoxFieldActions));

            //cloneSingle does not adds  Actions buttons for Cloneable Group,so we must do it here
            var boxActions = pCloneableGroup.find(sCloneableEntry)
                .first()
                .find(sMetaBoxFieldActions)
                .clone(true, true);

            //add  actions to latest cloned field group
            boxOfClones.children('.title').prepend(btnSortable.clone(true, true));
            boxOfClones.children('.inside').append(boxActions);
            boxOfClones.find('.hide').removeClass('hide');

        }

        function incrementAttr(attr, baseValue) {
            var value = baseValue + "_" + 1;
            for (var i = 2; $("[" + attr + "=" + value + "]").length > 0; i++) {
                value = baseValue + "_" + i;
            }
            return value;
        }

        function incrementLabel(searchScope, baseText) {
            var text = baseText + " #" + 1;
            var labels = searchScope.find('label');
            var i = 2;

            while (findText(labels, text)) {
                text = baseText + " #" + i;
                i++;
            }

            return text;
        }

        function findText(scope, text) {
            var filtered = scope.filter(function () {
                return $(this).text() === text;
            });

            return filtered.length > 0;
        }

        return {
            init: function () {
                initMetaboxes();
                initSortable();
                cloneableActionEvents();
            }
        }
    })();


    wp_stem_meta_field.init();
});