/**
 * This will help reorganize elements inside a parent container based on attributes provided
 * @type {{init}}
 */
var SortElements = function($) {

    /**
     *
     * @constructor
     */
    var Main = function() {
        $.fn.sortElements = (function () {

            var sort = [].sort;

            return function (comparator, getSortable) {

                getSortable = getSortable || function () {
                    return this;
                };

                var placements = this.map(function () {

                    var sortElement = getSortable.call(this),
                        parentNode = sortElement.parentNode,

                        // Since the element itself will change position, we have
                        // to have some way of storing its original position in
                        // the DOM. The easiest way is to have a 'flag' node:
                        nextSibling = parentNode.insertBefore(
                            document.createTextNode(''),
                            sortElement.nextSibling);

                    return function () {

                        if (parentNode === this) {
                            throw new Error(
                                "You can't sort elements if any one is a descendant of another.");
                        }

                        // Insert before flag:
                        parentNode.insertBefore(this, nextSibling);
                        // Remove flag:
                        parentNode.removeChild(nextSibling);

                    };

                });

                return sort.call(this, comparator).each(function (i) {
                    placements[i].call(getSortable.call(this));
                });

            };

        })();
    };

    return {
        init: function() {
            if(!window.$ && !window.jQuery) {
                console.warn('[jQuery] Required For Class To Successfully Extend The Library.');
            } else {
                Main();
            }
        },
        sort: function(container, selector, attribute, option) {

            if(option === null || option === 'asc') {
                $(container).append($(selector).sort(function(a,b){
                    return parseInt(a.getAttribute(attribute))-parseInt(b.getAttribute(attribute))
                }));
            } else if(option === 'desc') {
                $(container).append($(selector).sort(function(a,b){
                    return parseInt(b.getAttribute(attribute))-parseInt(a.getAttribute(attribute))
                }));
            }

        }
    }
}(jQuery);
SortElements.init();