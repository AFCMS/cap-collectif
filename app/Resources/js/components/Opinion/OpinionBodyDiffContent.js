// @flow
import * as React from 'react';
import { graphql, createFragmentContainer } from 'react-relay';
import OpinionBodyDiffModal from './OpinionBodyDiffModal';
import type { OpinionBodyDiffContent_opinion } from './__generated__/OpinionBodyDiffContent_opinion.graphql';

type Props = {
  opinion: OpinionBodyDiffContent_opinion,
};

class OpinionBodyDiffContent extends React.Component<Props> {
  render() {
    const opinion = this.props.opinion;

    if (!opinion.modals || opinion.modals.length < 1) {
      return <div dangerouslySetInnerHTML={{ __html: opinion.body }} />;
    }

    const modals = opinion.modals;
    const sections = [];

    if (opinion.body) {
      opinion.body.split('<p>').forEach(sentence => {
        if (sentence.length > 0) {
          sections.push(sentence.replace('</p>', ''));
        }
      });
    }
    const parts = [];
    sections.forEach(section => {
      let foundModal = false;
      modals.forEach(modal => {
        if (modal && section.indexOf(modal.key) !== -1) {
          foundModal = modal;
        }
      });
      if (!foundModal) {
        parts.push({
          content: section,
          link: false,
        });
      } else {
        parts.push({
          before: section.slice(0, section.indexOf(foundModal.key)),
          link: foundModal.key,
          after: section.slice(section.indexOf(foundModal.key) + foundModal.key.length),
          modal: foundModal,
        });
      }
    });

    return (
      <div>
        {parts.map((part, index) => {
          if (!part.link) {
            return <p dangerouslySetInnerHTML={{ __html: part.content }} />;
          }
          return (
            <p key={index}>
              <span dangerouslySetInnerHTML={{ __html: part.before }} />
              <OpinionBodyDiffModal link={part.link} modal={part.modal} />
              <span dangerouslySetInnerHTML={{ __html: part.after }} />
            </p>
          );
        })}
      </div>
    );
  }
}

export default createFragmentContainer(OpinionBodyDiffContent, {
  opinion: graphql`
    fragment OpinionBodyDiffContent_opinion on OpinionOrVersion {
      ... on Opinion {
        body
        modals {
          key
          before
          after
          ...OpinionBodyDiffModal_modal
        }
      }
    }
  `,
});
