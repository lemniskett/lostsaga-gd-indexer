#!/usr/bin/env python3

import argparse
import requests
import io
import os
import sys
from pathlib import Path
from PIL import Image, UnidentifiedImageError

import logging
logging.basicConfig(level=logging.INFO, format='%(asctime)s %(levelname)s => %(message)s')

GEAR_BASE_URL = {
    'lso': 'http://43.129.51.47:9000/gear'
}

# https://github.com/Trisnox/lsc2dds-py/blob/main/main.py
def convert_lsc_to_dds(lsc):
    return [(byte >> 3 | ((byte & 0x7) << 5)) ^ 0xff for byte in lsc]


def save_dds_to_jpg(dds, file_name):
    with Image.open(io.BytesIO(bytearray(dds))) as img:
        img = img.convert("RGB")
        img.save(file_name, format='jpeg')


def fetch_lsc(url):
    try:
        res = requests.get(url)
        res.raise_for_status()
        content_length_kb = len(res.content) / 1024
        logging.info(f'fetched {url} with size {content_length_kb} kb')
        return res.content
    except requests.HTTPError as e:
        logging.debug(e)
        return None


def scrape_design(export_dir, server, pagination, index):
    page = index // pagination + 1
    Path(f'{export_dir}/textures/{server}/{page}').mkdir(parents=True, exist_ok=True)
    part_index = 0
    while True:
        part_index += 1
        url = f'{GEAR_BASE_URL[server]}/{index}_{part_index}.lsc'
        lsc = fetch_lsc(url)
        if lsc is None:
            if part_index == 1:
                logging.debug(f'url not found {url}')
                return False
            break
        dds = convert_lsc_to_dds(lsc)

        jpg_path = f'{export_dir}/textures/{server}/{page}/{index}_{part_index}.jpg'
        save_dds_to_jpg(dds, jpg_path)
    return True


def get_latest_page(export_dir, server):
    search_dir = f'{export_dir}/textures/{server}'
    newest_dir = None
    newest_time = 0
    for entry in os.scandir(search_dir):
        if entry.is_dir():
            dir_creation_time = os.path.getctime(entry.path)
            if dir_creation_time > newest_time:
                newest_dir = entry.name
                newest_time = dir_creation_time
    return int(newest_dir) if newest_dir else 1


def main():
    parser = argparse.ArgumentParser(prog='Lost Saga GD Indexer')
    parser.add_argument('-s', '--server', choices=GEAR_BASE_URL.keys(), default='lso')
    parser.add_argument('-e', '--export-dir', type=str, default='public_html')
    parser.add_argument('-f', '--start-from', type=int, default=1)
    parser.add_argument('-c', '--continue-from-last', action='store_true')
    parser.add_argument('-n', '--not-found-toleration', type=int, default=100)
    parser.add_argument('-p', '--pagination', type=int, default=100)
    args = parser.parse_args()

    if args.start_from != 1 and args.continue_from_last:
        print('cannot use --start-from and --continue-from-last at the same time', file=sys.stderr)
        exit(1)


    if args.continue_from_last:
        gear_index = (get_latest_page(args.export_dir, args.server) - 1) * args.pagination
    else:
        gear_index = args.start_from
    
    not_found_count = 0

    while True:
        logging.info(f'fetching gear design #{gear_index}')
        if scrape_design(args.export_dir, args.server, args.pagination, gear_index):
            not_found_count = 0
        else:
            logging.info(f'failed to fetch gear design #{gear_index}')
            not_found_count += 1
        if not_found_count > args.not_found_toleration:
            logging.info('too many not found, stopping')
            exit(0)
        gear_index += 1


if __name__ == '__main__':
    main()