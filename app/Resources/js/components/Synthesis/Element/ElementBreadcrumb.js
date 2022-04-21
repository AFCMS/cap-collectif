import React from 'react';
import { IntlMixin } from 'react-intl';
import ElementTitle from './ElementTitle';

const ElementBreadcrumb = React.createClass({
  propTypes: {
    element: React.PropTypes.object,
    link: React.PropTypes.string,
  },
  mixins: [IntlMixin],

  getDefaultProps() {
    return {
      link: 'edition',
    };
  },

  getElementBreadcrumbItems(element) {
    const items = [];
    if (element.path) {
      element.path.split('|').map((data) => {
        const splitted = data.split('-');
        const title = splitted.slice(0, splitted.length - 5).join('-');
        const id = splitted.slice(splitted.length - 5, splitted.length).join('-');
        const item = {
          'title': title || null,
          id,
        };
        items.push(item);
      });
      return items;
    }
    items.push({
      id: null,
      title: '(...)',
    });
    if (element.parent) {
      items.push(element.parent);
    }
    items.push(element);
    return items;
  },

  renderBreadCrumbItem(element, index) {
    return (
      <span key={index} className="element__breadcrumb-item">
        <span className="element__breadcrumb-arrow"> > </span>
        <ElementTitle element={element} link={this.props.link} />
      </span>
    );
  },

  render() {
    const items = this.getElementBreadcrumbItems(this.props.element);
    return (
      <p className="element__breadcrumb">
        <i className="cap cap-folder-2"></i>
        {
          items.map((element, index) => {
            return this.renderBreadCrumbItem(element, index);
          })
          }
      </p>
    );
  },


});

export default ElementBreadcrumb;
