// @flow
import React from 'react';
import { connect } from 'react-redux';
import { graphql, createFragmentContainer } from 'react-relay';
import ShareButtonDropdown from '../Utils/ShareButtonDropdown';
import ArgumentVoteBox from './Vote/ArgumentVoteBox';
import ArgumentEditModal from './Edition/ArgumentEditModal';
import ArgumentDeleteModal from './Deletion/ArgumentDeleteModal';
import ArgumentReportButton from './ArgumentReportButton';
import EditButton from '../Form/EditButton';
import DeleteButton from '../Form/DeleteButton';
import { openArgumentEditModal } from '../../redux/modules/opinion';
import type { ArgumentButtons_argument } from './__generated__/ArgumentButtons_argument.graphql';

type Props = {
  argument: ArgumentButtons_argument,
  dispatch: Function,
};

type State = {
  isDeleting: boolean,
};

class ArgumentButtons extends React.Component<Props, State> {
  state = {
    isDeleting: false,
  };

  openDeleteModal = () => {
    this.setState({ isDeleting: true });
  };

  closeDeleteModal = () => {
    this.setState({ isDeleting: false });
  };

  render() {
    const { argument, dispatch } = this.props;
    return (
      <div>
        {/* $FlowFixMe */}
        <ArgumentVoteBox argument={argument} /> <ArgumentReportButton argument={argument} />{' '}
        <EditButton
          onClick={() => {
            dispatch(openArgumentEditModal(argument.id));
          }}
          author={argument.author}
          editable={argument.contribuable}
          className="argument__btn--edit btn-xs btn-dark-gray btn--outline"
        />
        <ArgumentEditModal argument={argument} />{' '}
        <DeleteButton
          onClick={this.openDeleteModal}
          author={argument.author}
          className="argument__btn--delete btn-xs"
        />
        <ArgumentDeleteModal
          argument={argument}
          show={this.state.isDeleting}
          onClose={this.closeDeleteModal}
        />{' '}
        {/* $FlowFixMe */}
        <ShareButtonDropdown
          id={`arg-${argument.id}-share-button`}
          url={argument.url}
          className="argument__btn--share btn-dark-gray btn--outline btn btn-xs"
        />
      </div>
    );
  }
}

const container = connect()(ArgumentButtons);
export default createFragmentContainer(
  container,
  graphql`
    fragment ArgumentButtons_argument on Argument {
      author {
        id
        displayName
      }
      id
      contribuable
      url
      ...ArgumentEditModal_argument
      ...ArgumentVoteBox_argument
      ...ArgumentReportButton_argument
    }
  `,
);
