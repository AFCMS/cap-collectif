import React, { PropTypes } from 'react';
import { IntlMixin } from 'react-intl';
import FormMixin from '../../../utils/FormMixin';
import DeepLinkStateMixin from '../../../utils/DeepLinkStateMixin';
import FlashMessages from '../../Utils/FlashMessages';
import Input from '../../Form/Input';

const IdeaVoteForm = React.createClass({
  displayName: 'IdeaVoteForm',
  propTypes: {
    idea: PropTypes.object.isRequired,
    serverErrors: PropTypes.array,
    anonymous: PropTypes.bool.isRequired,
  },
  mixins: [IntlMixin, DeepLinkStateMixin, FormMixin],

  getDefaultProps() {
    return {
      serverErrors: [],
    };
  },

  getInitialState() {
    return {
      form: {
        username: '',
        email: '',
        comment: '',
        private: false,
      },
      errors: {
        username: [],
        email: [],
        comment: [],
        private: [],
      },
    };
  },

  componentDidMount() {
    this.updateAnonymousConstraints(this.props.anonymous);
  },

  componentWillReceiveProps(nextProps) {
    if (nextProps.anonymous !== this.props.anonymous) {
      this.updateAnonymousConstraints(nextProps.anonymous);
    }
  },

  updateAnonymousConstraints(anonymous) {
    this.formValidationRules = {};
    if (anonymous) {
      this.formValidationRules = {
        username: {
          min: { value: 2, message: 'idea.vote.constraints.username' },
          notBlank: { message: 'idea.vote.constraints.username' },
        },
        email: {
          notBlank: { message: 'idea.vote.constraints.email' },
          isEmail: { message: 'idea.vote.constraints.email' },
        },
      };
    }
  },

  reinitState() {
    this.setState(this.getInitialState);
  },

  formValidationRules: {},

  userHasVote() {
    return !this.props.anonymous && this.props.idea.userHasVote;
  },

  renderFormErrors(field) {
    const errors = this.getErrorsMessages(field);
    if (errors.length === 0) {
      return null;
    }
    return <FlashMessages errors={errors} form />;
  },

  render() {
    const { anonymous, serverErrors, idea } = this.props;

    return (
      <form ref={(c) => this.form = c}>

        <FlashMessages errors={serverErrors} translate={false} />

        {
          anonymous
            ? <Input
            id="idea-vote-username"
            type="text"
            name="idea-vote__username"
            valueLink={this.linkState('form.username')}
            label={`${this.getIntlMessage('idea.vote.form.username')} *`}
            groupClassName={this.getGroupStyle('username')}
            errors={this.renderFormErrors('username')}
          />
            : null
        }

        {
          anonymous
            ? <Input
              id="idea-vote-email"
              type="text"
              name="idea-vote__email"
              valueLink={this.linkState('form.email')}
              label={`${this.getIntlMessage('idea.vote.form.email')} *`}
              groupClassName={this.getGroupStyle('email')}
              errors={this.renderFormErrors('email')}
            />
            : null
        }

        {
          (idea.commentable && !this.state.form.private && (anonymous || !this.userHasVote()))
            ? <Input
              id="idea-vote-comment"
              type="textarea"
              name="idea-vote__comment"
              valueLink={this.linkState('form.comment')}
              label={this.getIntlMessage('idea.vote.form.comment')}
              placeholder={this.getIntlMessage('idea.vote.form.comment_placeholder')}
              groupClassName={this.getGroupStyle('comment')}
              errors={this.renderFormErrors('comment')}
            />
            : null
        }

        {
          (this.state.form.comment.length > 0 || (!anonymous && this.userHasVote()))
            ? null
            : <Input
              id="idea-vote-private"
              type="checkbox"
              name="idea-vote__private"
              checkedLink={this.linkState('form.private')}
              label={this.getIntlMessage('idea.vote.form.private')}
              groupClassName={this.getGroupStyle('private')}
              errors={this.renderFormErrors('private')}
            />
        }

      </form>
    );
  },

});

export default IdeaVoteForm;
