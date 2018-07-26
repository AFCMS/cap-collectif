// @flow
import React from 'react';
import classNames from 'classnames';
import AnswerBody from '../Answer/AnswerBody';

type Props = {
  answer?: Object,
};

class OpinionAnswer extends React.Component<Props> {
  render() {
    const answer = this.props.answer;
    if (!answer) {
      return null;
    }
    const classes = classNames({
      opinion__answer: true,
      'bg-vip': answer.author && answer.author.vip,
    });
    return (
      <div className={classes} id="answer">
        {answer.title ? (
          <p className="h4" style={{ marginTop: '0' }}>
            {answer.title}
          </p>
        ) : null}
        <AnswerBody answer={answer} />
      </div>
    );
  }
}

export default OpinionAnswer;
