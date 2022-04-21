import React, { PropTypes } from 'react';
import { IntlMixin } from 'react-intl';
import classNames from 'classnames';
import AnswerBody from '../../Answer/AnswerBody';

const ProposalPageAnswer = React.createClass({
  propTypes: {
    answer: PropTypes.object,
    className: PropTypes.string,
  },
  mixins: [IntlMixin],

  getDefaultProps() {
    return {
      answer: null,
      className: '',
    };
  },

  render() {
    const answer = this.props.answer;
    if (!answer) {
      return null;
    }
    const classes = {
      'bg-vip': answer.author && answer.author.vip,
      'block': true,
      [this.props.className]: true,
    };
    return (
      <div className={classNames(classes)}>
        {
          answer.title && <h2 className="h2">{answer.title}</h2>
        }
        <AnswerBody answer={answer} />
      </div>
    );
  },

});

export default ProposalPageAnswer;
