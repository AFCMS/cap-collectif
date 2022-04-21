import React from 'react';
import { IntlMixin } from 'react-intl';
import { Button } from 'react-bootstrap';

const NotationButtons = React.createClass({
  propTypes: {
    notation: React.PropTypes.number,
    onChange: React.PropTypes.func,
    block: React.PropTypes.bool,
  },
  mixins: [IntlMixin],

  getNotationStarsClasses() {
    const classes = [];
    for (let i = 0; i < 5; i++) {
      if (i < this.props.notation) {
        classes[i] = 'active';
      }
    }
    return classes;
  },

  note(value) {
    if (typeof this.props.onChange === 'function') {
      this.props.onChange(value);
    }
  },

  render() {
    const classes = this.getNotationStarsClasses();
    return (
      <div className="element__action">
        <Button bsSize="large" className="element__action-notation" block={this.props.block}>
          <a className={classes[4]} id="notation-button-5" onClick={this.note.bind(this, 5)}>
            <i className="cap cap-star-1-1"></i>
          </a>
          <a className={classes[3]} id="notation-button-4" onClick={this.note.bind(this, 4)}>
            <i className="cap cap-star-1-1"></i>
          </a>
          <a className={classes[2]} id="notation-button-3" onClick={this.note.bind(this, 3)}>
            <i className="cap cap-star-1-1"></i>
          </a>
          <a className={classes[1]} id="notation-button-2" onClick={this.note.bind(this, 2)}>
            <i className="cap cap-star-1-1"></i>
          </a>
          <a className={classes[0]} id="notation-button-1" onClick={this.note.bind(this, 1)}>
            <i className="cap cap-star-1-1"></i>
          </a>
        </Button>
      </div>
    );
  },

});

export default NotationButtons;
