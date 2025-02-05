// @flow
import * as React from 'react';
import { createFragmentContainer, graphql, type RelayFragmentContainer } from 'react-relay';
import { useIntl } from 'react-intl';
import { ButtonQuickAction, CapUIIcon, Modal, CapUIModalSize } from '@cap-collectif/ui';
import type { ModalArgumentAuthorMenu_argument } from '~relay/ModalArgumentAuthorMenu_argument.graphql';
import ModalEditArgumentMobile from '~/components/Debate/Page/Arguments/ModalEditArgumentMobile';
import ModalDeleteArgumentMobile from '~/components/Debate/Page/Arguments/ModalDeleteArgumentMobile';

type Props = {|
  argument: ModalArgumentAuthorMenu_argument,
  hasViewer?: boolean,
|};

export const ModalArgumentAuthorMenu = ({ argument, hasViewer = true }: Props): React.Node => {
  const intl = useIntl();
  return (
    <Modal
      hideCloseButton
      alwaysOpenInPortal
      variantSize={CapUIModalSize.Xl}
      ariaLabel={intl.formatMessage({ id: 'global.menu' })}
      disclosure={
        <ButtonQuickAction
          icon={CapUIIcon.More}
          label={intl.formatMessage({ id: 'global.menu' })}
          variantColor="gray"
          border="none"
          height="32px"
        />
      }>
      {({ hide: hideModalMenu }) => (
        <>
          <Modal.Header />
          <Modal.Body spacing={6} p={6}>
            {hasViewer && (
              <ModalEditArgumentMobile argument={argument} hidePreviousModal={hideModalMenu} />
            )}
            <ModalDeleteArgumentMobile
              argument={argument}
              hidePreviousModal={hideModalMenu}
              hasViewer={hasViewer}
            />
          </Modal.Body>
        </>
      )}
    </Modal>
  );
};

export default (createFragmentContainer(ModalArgumentAuthorMenu, {
  argument: graphql`
    fragment ModalArgumentAuthorMenu_argument on AbstractDebateArgument {
      ...ModalEditArgumentMobile_argument
      ...ModalDeleteArgumentMobile_argument
    }
  `,
}): RelayFragmentContainer<typeof ModalArgumentAuthorMenu>);
