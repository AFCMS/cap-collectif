// @flow
import styled, { type StyledComponent } from 'styled-components';
import { MenuItem, Modal } from 'react-bootstrap';
import colors from '~/utils/colors';
import { mediaQueryMobile } from '~/utils/sizes';
import Icon from '~ds/Icon/Icon';

export const ProjectBoxContainer: StyledComponent<
  { color?: ?string },
  {},
  HTMLDivElement,
> = styled.div`
  border-top-color: ${({ color }) => color || '#858e95'};
`;

export const BoxContainer: StyledComponent<{ color?: ?string }, {}, HTMLDivElement> = styled.div`
  width: auto;
  margin: 0 2rem 2rem 2rem;
  border-top-color: ${({ color }) => color || '#858e95'};
`;

export const BoxDeprecated: StyledComponent<{}, {}, HTMLDivElement> = styled.div.attrs({
  className: 'box-content',
})`
  display: flex;
  justify-content: space-between;
  padding: 15px 0;

  > span {
    font-weight: bold;
  }

  a {
    font-weight: 600;
    display: flex;
    color: #0388cc;

    :hover,
    :focus {
      text-decoration: none;
    }

    > span {
      margin-right: 5px;

      :hover {
        text-decoration: underline;
      }
    }
  }

  span[class*='Label'] {
    padding: 1px 8px !important;
  }

  > div span + span {
    font-weight: bold;
    margin-left: 5px;
  }

  i {
    font-size: 10px;
    top: 5px;
    left: 2px;
  }
`;

export const ProjectBoxHeader: StyledComponent<
  { noBorder?: boolean },
  {},
  HTMLDivElement,
> = styled.div`
  color: ${colors.darkText};
  border-bottom: ${({ noBorder }) => !noBorder && `1px solid ${colors.lightGray};`};
  margin-bottom: ${({ noBorder }) => !noBorder && '20px;'};
  margin-top: ${({ noBorder }) => !noBorder && '15px;'};
  h4 {
    font-size: 18px;
    font-weight: bold;
  }
  h5 {
    font-size: 16px;
    font-weight: bold;
    .form-group {
      margin-bottom: 0;
    }
  }
`;

export const StepModalContainer: StyledComponent<{}, {}, typeof Modal> = styled(Modal).attrs({
  className: 'step__modal',
})`
  && .custom-modal-dialog {
    transform: none;
  }
`;

export const NoStepsPlaceholder: StyledComponent<{}, {}, HTMLDivElement> = styled.div`
  height: 50px;
  border: 1px solid ${colors.lightGray};
  border-radius: 4px;
  background: #fafafa;
  text-align: center;
  padding: 15px;
  color: ${colors.darkGray};
`;

export const ProjectSmallFieldsContainer: StyledComponent<{}, {}, HTMLDivElement> = styled.div`
  display: flex;
  flex: 1;
  flex-direction: row;
  > div {
    margin-right: 20px;
    min-width: 200px;
  }
  @media (max-width: ${mediaQueryMobile.maxWidth}) {
    flex-direction: column;
  }

  .form-group {
    width: 100%;
  }

  .rdt + .input-group-addon {
    /** We want to override the green on successful date field */
    color: unset;
    background: unset;
    border-color: #d2d6de;
  }

  .rdt > input {
    z-index: 0;
  }
`;

export const ProjectAccessContainer: StyledComponent<{}, {}, HTMLDivElement> = styled.div`
  .radio label {
    font-weight: bold;
  }
`;

export const ProjectSmallInput: StyledComponent<{}, {}, HTMLDivElement> = styled.div`
  width: 200px;
`;

export const StepMenuItem: StyledComponent<{}, {}, typeof MenuItem> = styled(MenuItem)`
  a {
    /** Just overriding some bootstrap */
    padding: 5px 20px !important;
    font-weight: 600 !important;
  }
`;

export const StepModalTitle: StyledComponent<{}, {}, typeof Modal.Title> = styled(Modal.Title)`
  font-weight: 600;
  font-size: 20px;
`;

export const PermalinkWrapper: StyledComponent<{}, {}, HTMLParagraphElement> = styled.p`
  word-break: break-all;
  margin: 0;
`;

export const UpdateSlugIcon: StyledComponent<{}, {}, typeof Icon> = styled(Icon)`
  cursor: pointer;
`;
