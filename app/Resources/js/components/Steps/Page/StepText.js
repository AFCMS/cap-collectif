import React from 'react';
/* import ReactDOM from 'react-dom'; */
import { FormattedHTMLMessage } from 'react-intl';
import ReadMoreLink from '../../Utils/ReadMoreLink';

const StepText = React.createClass({
  propTypes: {
    text: React.PropTypes.string,
  },

  getDefaultProps() {
    return {
      text: null,
    };
  },

  getInitialState() {
    return {
      expanded: true,
      truncated: false,
      hideText: false,
    };
  },

  componentDidMount() {
    /* const totalHeight = ReactDOM.findDOMNode(this.refs.content).offsetHeight;
    // Text should be truncated
    if (totalHeight > 105) {
      this.setState({
        hideText: false,
        truncated: true,
        expanded: false,
      });
      return;
    }
    // Text is not truncated
    this.setState({
      hideText: false,
      truncated: false,
      expanded: true,
    }); */
  },

  toggleExpand() {
    this.setState({
      expanded: !this.state.expanded,
    });
  },

  render() {
    const { text } = this.props;
    if (!text) {
      return null;
    }
    const style = {
      maxHeight: this.state.expanded ? 'none' : '85px',
      visibility: this.state.hideText ? 'hidden' : 'visible',
    };
    return (
      <div className="step__intro" >
        <div className="opinion  opinion--current  opinion--default">
          <div className="box">
            <div ref="content" className="step__intro__content" style={style}>
              <FormattedHTMLMessage message={text} />
            </div>
          </div>
          <div className="text-center">
            <ReadMoreLink
              visible={this.state.truncated}
              expanded={this.state.expanded}
              onClick={this.toggleExpand}
            />
          </div>
        </div>
      </div>
    );
  },

});

export default StepText;
