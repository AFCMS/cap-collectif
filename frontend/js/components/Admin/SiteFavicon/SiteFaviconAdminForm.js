// @flow
import * as React from 'react';
import { FormattedMessage } from 'react-intl';
import { Field, reduxForm } from 'redux-form';
import { createFragmentContainer, graphql, type RelayFragmentContainer } from 'react-relay';
import { Button } from 'react-bootstrap';
import component from '../../Form/Field';
import type { MediaFromAPI } from '../../../types';
import { type SiteFaviconAdminForm_siteFavicon } from '~relay/SiteFaviconAdminForm_siteFavicon.graphql';
import ChangeSiteFaviconMutation from '../../../mutations/ChangeSiteFaviconMutation';
import AlertForm from '../../Alert/AlertForm';
import RemoveSiteFaviconMutation from '../../../mutations/RemoveSiteFaviconMutation';

type FormValues = {|
  +media: ?MediaFromAPI,
|};

type RelayProps = {|
  +siteFavicon: SiteFaviconAdminForm_siteFavicon,
|};

type Props = {|
  ...RelayProps,
  ...ReduxFormFormProps,
  +initialValues: FormValues,
|};

const formName = 'site-favicon-admin';

const onSubmit = async (values: FormValues) => {
  const { media } = values;

  if (media) {
    const mediaId = media.id;
    const input = {
      mediaId,
    };

    return ChangeSiteFaviconMutation.commit({ input });
  }

  return RemoveSiteFaviconMutation.commit({ input: {} });
};

export const SiteFaviconAdminForm = (props: Props): React.Node => {
  const {
    siteFavicon,
    handleSubmit,
    pristine,
    invalid,
    valid,
    submitSucceeded,
    submitFailed,
    submitting,
  } = props;
  document.querySelectorAll('link[rel="icon"]').forEach((node: HTMLElement) => {
    // eslint-disable-next-line no-undef
    if (node instanceof HTMLLinkElement) {
      node.href = siteFavicon && siteFavicon.media ? siteFavicon.media.url : '/favicon-96x96.png';
    }
  });

  return (
    <form onSubmit={handleSubmit}>
      <Field
        id="proposal_media"
        name="media"
        component={component}
        type="image"
        label={
          <span>
            <FormattedMessage id="global.image" />
          </span>
        }
        help={<FormattedMessage id="favicon-parameters" />}
      />
      <Button
        type="submit"
        id="site_favicon_save"
        bsStyle="primary"
        disabled={pristine || invalid || submitting}>
        <FormattedMessage id={submitting ? 'global.loading' : 'global.save'} />
      </Button>
      <AlertForm
        valid={valid}
        invalid={invalid}
        submitting={submitting}
        submitSucceeded={submitSucceeded}
        submitFailed={submitFailed}
      />
    </form>
  );
};

const form = (reduxForm({
  onSubmit,
  enableReinitialize: true,
  form: formName,
})(SiteFaviconAdminForm): React.AbstractComponent<{| +initialValues: FormValues, ...RelayProps |}>);

function injectProp(Component) {
  return function WrapperComponent(props: RelayProps) {
    const { siteFavicon } = props;
    return (
      <Component
        {...props}
        initialValues={{
          media: siteFavicon.media,
        }}
      />
    );
  };
}
const container = (injectProp(form): React.AbstractComponent<RelayProps>);

export default (createFragmentContainer(container, {
  siteFavicon: graphql`
    fragment SiteFaviconAdminForm_siteFavicon on SiteImage {
      id
      media {
        id
        url
        name
      }
    }
  `,
}): RelayFragmentContainer<typeof container>);
