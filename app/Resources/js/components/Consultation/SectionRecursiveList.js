// @flow
import * as React from 'react';
import { graphql, createFragmentContainer } from 'react-relay';
import SectionList from './SectionList';
import UnpublishedOpinionList from './UnpublishedOpinionList';
import type { SectionRecursiveList_consultation } from './__generated__/SectionRecursiveList_consultation.graphql';

type Props = {
  consultation: SectionRecursiveList_consultation,
};

export class SectionRecursiveList extends React.Component<Props> {
  render() {
    const { consultation } = this.props;

    return (
      <div className="consultationContent">
        {/* $FlowFixMe */}
        <UnpublishedOpinionList consultation={consultation} />
        {consultation.sections &&
          consultation.sections
            .filter(Boolean)
            .map((section, index) => (
              <SectionList key={index} consultation={consultation} section={section} level={0} group={index+1}/>
            ))}
      </div>
    );
  }
}

export default createFragmentContainer(
  SectionRecursiveList,
  graphql`
    fragment SectionRecursiveList_consultation on Consultation {
      ...UnpublishedOpinionList_consultation
      ...Section_consultation
      sections {
        ...Section_section
        sections {
          ...Section_section
          sections {
            ...Section_section
            sections {
              ...Section_section
              sections {
                ...Section_section
              }
            }
          }
        }
      }
    }
  `,
);
